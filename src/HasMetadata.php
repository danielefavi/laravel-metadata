<?php

namespace DanieleFavi\Metadata;

trait HasMetadata
{

    /**
     * The meta relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function metas()
    {
        return $this->morphMany(\DanieleFavi\Metadata\Meta::class, 'model');
    }

    /**
     * Save a meta value: if the meta value does not exist then it is
     * going to be created, updated otherwise.
     *
     * @param string $key
     * @param mixed $value
     * @return Meta
     */
    public function saveMeta(string $key, $value=null): Meta
    {
        if ($meta = $this->getMetaObj($key)) {
            if ($meta->value !== $value) {
                $meta->value = $value;
                $meta->save();
            }
        } else {
            $meta = $this->metas()->create([
                'key' => $key,
                'value' => $value,
            ]);
        }

        return $meta;
    }

    /**
     * Save an array of key => value as meta key => meta value.
     *
     * @param array $metas
     * @return void
     */
    public function saveMetas(array $metas=null)
    {
        if (empty($metas)) {
            return;
        }

        foreach ($metas as $key => $value) {
            $this->saveMeta($key, $value);
        }
    }

    /**
     * Return the value of the meta for the give key.
     * Return the $default if the meta key is not found
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return void
     */
    public function getMeta(string $key, $default=null)
    {
        if ($meta = $this->getMetaObj($key)) {
            return $meta->value;
        }

        return $default;
    }

    /**
     * Return the array key=>value of the meta values of the model for the
     * given keys.
     *
     * @param array $keys|null
     * @return mixed
     */
    public function getMetas(array $keys=null)
    {
        if ($keys) {
            $metas = $this->metas()
                ->whereIn('key', $keys)
                ->get();
        } else {
            $metas = $this->metas;
        }

        return $metas->reduce(function ($carry, $item) {
            return ($carry ?? []) + [$item->key => $item->value];
        });
    }

    /**
     * Check if the model has a meta value.
     *
     * @param string $key
     * @return boolean
     */
    public function hasMeta(string $key): bool
    {
        return (bool)$this->metas()->where('key', $key)->count();
    }

    /**
     * Return the meta model related to the given key.
     *
     * @param string $key
     * @return mixed
     */
    public function getMetaObj(string $key): ?Meta
    {
        return $this->metas()->where('key', $key)->first();
    }

    /**
     * Delete a meta (or metas) for a given key (or keys).
     *
     * @param mixed $key
     * @return boolean
     */
    public function deleteMeta($key)
    {
        if (is_array($key)) {
            return $this->metas()->whereIn('key', $key)->delete();
        }

        return  $this->metas()->where('key', $key)->delete();
    }

    /**
     * Delete all metadata related to the model.
     *
     * @return void
     */
    public function deleteAllMeta()
    {
        return  $this->metas()->delete();
    }

    /**
     * Scope function for querying the metadata. Example:
     * User::metaWhere('some_key', 'like', '%_some_value_%')->get();
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|int|float $metaKey
     * @param mixed $metaValue
     * @return void
     */
    public function scopeMetaWhere($query, $metaKey, $metaValue)
    {
        list($metaKey, $metaValue, $condition) = $this->getMetaWhereArgs(func_get_args());
 
        return $query->whereHas('metas', function ($subQuery) use ($metaKey, $metaValue, $condition) {
            $subQuery->where('key', $metaKey);
 
            if ($condition === null) {
                $subQuery->where('value', $metaValue);
            } else {
                $subQuery->where('value', $condition, $metaValue);
            }
        });
    }
 
    /**
     * Scope function for querying the metadata. Example
     * User::metaWhere('hair_color', 'brown')
     *      ->orMetaWhere('hair_color', 'pink')
     *      ->get();
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|int|float $metaKey
     * @param mixed $metaValue
     * @return void
     */
    public function scopeOrMetaWhere($query, $metaKey, $metaValue)
    {
        list($metaKey, $metaValue, $condition) = $this->getMetaWhereArgs(func_get_args());
 
        return $query->orWhereHas('metas', function ($subQuery) use ($metaKey, $metaValue, $condition) {
            $subQuery->where('key', $metaKey);
 
            if ($condition === null) {
                $subQuery->where('value', $metaValue);
            } else {
                $subQuery->where('value', $condition, $metaValue);
            }
        });
    }
 
    /**
     * Return the list of arguments passed to the scope function.
     *
     * @param array $args
     * @return array
     */
    private function getMetaWhereArgs(array $args): array
    {
        // removing all instances of the query builder from the arguments: the firsts arguments passed
        // to the scope function are instances of Builder and they can be as many are the sub-queries
        $argsFiltered = array_values( array_filter($args, function($arg) {
            return !($arg instanceof \Illuminate\Database\Eloquent\Builder);
        }) );

        // the first filtered argument is the meta key
        $metaKey = $argsFiltered[0];
 
        if (count($args) > 2) {
            // the second position can be the condition, EG: "like" or ">=" or "<=" or ...
            // and the third position the meta value
            // EXAMPLE: User::metaWhere('some_key', 'like', '%_some_value_%')
            $condition = $args[1];
            $metaValue = json_encode($args[2]); // the metavalue are stored as json in the DB
        } else {
            // or the second position can be the metavalue and the condition is by default "="
            // EXAMPLE: User::metaWhere('some_key', 'some_value')
            $metaValue = json_encode($args[1]); // the metavalue are stored as json in the DB
            $condition = null;
        }
 
        return [ $metaKey, $metaValue, $condition ];
    }

}
