# Laravel Metadata - Metadata for all your models

Laravel Metadata let you store extra data using your model without adding any extra field to your model.

```php
$user->saveMeta('age', 25); // storing
$user->saveMeta('dog_name', 'Buddy'); // storing

$age = $user->getMeta('age');
```

## Installation

Install the package via composer:

```sh
composer require danielefavi/laravel-metadata
```

Then add in the `config/app.php` file the entry `DanieleFavi\Metadata\MetaServiceProvider::class,` in the `providers` section:

```php
    'providers' => [
        // ...
        DanieleFavi\Metadata\MetaServiceProvider::class,
    ],
```

Then run the migration:

```sh
php artisan migrate
```

## How to use *Laravel Metadata* in your model

After installing the package you have to add the trait `HasMetadata` in your model.

For example 

```php
use DanieleFavi\Metadata\HasMetadata; // to add in your model

class User extends Authenticatable
{
    use HasFactory;
    use HasMetadata; // to add in your model
    
    // ...
}
```

Now your model has all the methods to handle the metadata.

## Usage

### Saving the metadata

Using the method `saveMeta` you can save (store or update) a metadata:

```php
$user->saveMeta('phone_number', '111222333'); // storing a value
$user->saveMeta('color_preference', ['orange', 'yellow']); // storing an array
```

With `saveMetas` you can save multiple metadata at once:

```php
$user->saveMetas([
    'phone_number' => '333222111',
    'color_preference' => ['red', 'green'],
    'address' => '29 Arlington Avenue',
]);
```

### Getting the metadata

The method `getMeta` retrieve the metadata for the given key:

```php
$phoneNumber = $user->getMeta('phone_number'); // the value of $phoneNumber is '111222333'

// Default value in case the metadata has not been found (default: null)
$anotherMeta = $user->getMeta('another_meta', 10);
```

You can retrieve the metadata in bulk 

```php
// return an array key => value with the metadata specified for the given keys
$metas = $user->getMetas(['phone_number', 'address']);
// the value of the $metas is an array key => value:
// [
//     'phone_number' => '111222333',
//     'address' => '29 Arlington Avenue'
// ]

// return an array key => value containing all metadata of the user model
$metas = $user->getMetas();
```

### Getting the meta object by key

If you need to get the metadata object (and not just the value as `getMeta` and `getMetas`) you can use the method `getMetaObj`:

```php
$metaObj = $user->getMetaObj('address');
```

### Deleting the metadata

```php
// delete a single metadata
$user->deleteMeta('address');

// delete multiple metadata
$user->deleteMeta(['phone_number', 'address']);

// delete all metadata
$user->deleteAllMeta();
```

### Getting the meta objects of the model

Getting the collection of metadata attached to the model:

```php
$list = $user->metas;
```

### Querying

Getting all the users that have the hair color brown or pink.

```php
$users = User::metaWhere('hair_color', 'brown')
            ->orMetaWhere('hair_color', 'pink')
            ->get();
```
Getting all the users with the hair color brown or pink and with a dog named Charlie:

```php
$users = User::metaWhere('dog_name', 'charlie')
            ->where(function($query) {
                return $query->metaWhere('hair_color', 'brown')
                    ->orMetaWhere('hair_color', 'pink');
            })
            ->get();
```

### Advanced Metadata Query

You can query the metadata using `has`, `whereHas` and `with`. For example:

```php
$users = User::whereHas('metas', function($query) {
    $query->where('key', 'hair_color');
    $query->where('value', json_encode('blue')); // remember to json_encode the value!!!
})->get();

```

**Important**: when doing your custom queries remember to JSON encode the metavalue because in the DB the metavalue is stored as JSON.  
In the example above

## Eager Loading the Metadata

Getting all the users with all their metadata:

```php
$users = User::with('metas')->get();
```

Or lazy loading the metadata:

```php
$user = User::find(1);

$user->load('metas');
```
