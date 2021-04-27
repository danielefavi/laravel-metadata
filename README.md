# Laravel Metadata - Metadata for all your models

Laravel Metadata let you store extra data using your model without adding any extra field to your model.

```php
$user->saveMeta('age', 25); // storing
$user->saveMeta('dog_name', 'Buddy'); // storing

$age = $user->getMeta('age);
```

## Installation

Install the package via composer:

```sh
composer require danielefavi/meta
```

Then run the migration:

```sh
php artisan migrate
```

## Configuration

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
$phoneNumber = $user->getMeta('phone_number');

// Default value in case the metadata has not been found (default: null)
$anotherMeta = $user->getMeta('another_meta', 10);
```

You can retrieve the metadata in bulk 

```php
// return the metadata for the given keys
$metas = $user->getMetas(['phone_number', 'address']);

// return all metadata of the user model
$metas = $user->getMetas();
```

### Getting the meta object by key

If you need to get the metadata object (and not just the value as `getMeta` and `getMetas`) you can use the method `getMetaObj`:

```php
$metaObj = $user->getMetaObj('address');
```

### Getting the meta objects of the model

Getting the collection of metadata attached to the model:

```php
$list = $user->metas;
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