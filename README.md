<h1 align="center" style="color: #87CEEB">laravel-data-validator</h1>

<p align="center">
  <a href="https://github.com/aneterial/laravel-data-validator/actions"><img src="https://github.com/aneterial/laravel-data-validator/actions/workflows/tests.yml/badge.svg" alt="Build Status"></a>
  <a href="https://github.com/aneterial/laravel-data-validator?tab=MIT-1-ov-file"><img src="https://img.shields.io/badge/license-MIT-8ebb13?style=flat" alt="License"></a>
  <a href="https://packagist.org/packages/aneterial/laravel-data-validator"><img src="https://img.shields.io/badge/packagist-v1.0.0-blue?style=flat" alt="Packagist"></a>
  <a href="https://www.php.net/releases/8.2/en.php"><img src="https://img.shields.io/badge/php-%5E8.2-7a86b8?style=flat&logo=php" alt="PHP"></a>
  <a href="https://laravel.com/"><img src="https://img.shields.io/badge/laravel-%5E11-f9332b?style=flat&logo=laravel" alt="Laravel"></a>
</p>

## Contents
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Usage](#usage)
- [Examples](#examples)
- [Restrictions](#restrictions)
---

## Prerequisites
- PHP 8.2 or higher
- Laravel 11

for dev
- PHPUnit &11
- phpstan ^1.11
- php-cs-fixer ^3.58

## Installation
Install package via composer
```bash
composer require aneterial/laravel-data-validator
```
## Usage
You now have a class attribute `DataValidator\Attributes\RequestProperty` at your disposal. Add it to the properties of your DTO and set the necessary configuration fields
```php
use DataValidator\Attributes\RequestProperty;

final readonly class ExampleDTO {
  #[RequestProperty(property: 'id', rules: 'required|integer|min:0')]
  public int $id;

  #[RequestProperty(property: 'email', rules: 'required|string|email')]
  public string $email;
}
```
Description of fields:
- `property`: name of the request key that matches the property
- `rules`: validation rules based on component semantics [Laravel Validation](https://laravel.com/docs/11.x/validation), accepts only string value
- `requestDataType`: to indicate where a field is expected - in the request body (default) `const RequestProperty::BODY_TYPE` or in query string `const RequestProperty::QUERY_TYPE`
- `listRules`: if the value is an array (list) - set the validation rules for each element according to the semantics of [Laravel Validation](https://laravel.com/docs/11.x/validation)

Next, you need to get a `DataValidator\DataManager` instance in your controller from app DI container and pass the request essence to it, indicating the DTO class that you expect to receive after validation and filling with data.
```php
$dataManager = app(\DataValidator\DataManager::class);
```
Next, the Laravel validator will check the request entity (`instanse of \Illuminate\Http\Request`), and if the data is incorrect, it will throw an `\Illuminate\Validation\ValidationException`. If the data is correct, the Manager will create and fill the DTO object with data, which you can use in your application
```php
/** @var ExampleDTO $dto */
$dto = $dataManager->validateAndConvert(from: $request, to: ExampleDTO::class);
```
If your endpoint involves passing array of objects `[{...}, {...}, {...}]`, you can use a method that will validate the request and return an array of DTOs
```php
/** @var ExampleDTO[] $dtos */
$dtos = $dataManager->validateAndConvertList(from: $request, to: ExampleDTO::class);
```

## Examples
Here are some examples of using validation by attributes

1) DTO with lists
```php
final readonly class ExampleDTO {
...

  /** @var string[] $emails */
  #[RequestProperty(property: 'emails', rules: 'required|list', listRules: 'string|email')]
  public array $emails;
  
  /** @var int[] $ids */
  #[RequestProperty(property: 'ids', rules: 'required|list', listRules: 'int|min:0')]
  public array $ids;

...
}
```
2) DTO with non required properties, if it not required - it should be nullable, except array - it can be empty array
```php
final readonly class ExampleDTO {
  #[RequestProperty(property: 'id', rules: 'integer|min:0')]
  public ?int $id;

  #[RequestProperty(property: 'email', rules: 'string|email')]
  public ?string $email;

  /** @var int[] $ids */
  #[RequestProperty(property: 'ids', rules: 'list', listRules: 'int|min:0')]
  public array $ids;
}
```
3) DTO with nested object
```php
final readonly class ExampleDTO {
...

  #[RequestProperty(property: 'child', rules: 'required|array')]
  public NestedDTO $child;

...
}

// NestedDTO should contain properties with attributes
final readonly class NestedDTO {
  #[RequestProperty(property: 'id', rules: 'required|integer|min:0')]
  public int $id;

  #[RequestProperty(property: 'email', rules: 'required|string|email')]
  public string $email;
}
```
4) DTO with list of nested object
```php
final readonly class ExampleDTO {
...

  /** @var NestedDTO[] $children */
  #[RequestProperty(property: 'children', rules: 'required|list', listRules: NestedDTO::class)]
  public array $children;

...
}
```
5) DTO with enum of `BackedEnum` property, you should set enum type to property and no more rules are required except, if you want - indicate type of enum
```php
final readonly class ExampleDTO {
...

  #[RequestProperty(property: 'enum', rules: 'required|string')]
  public AnApplicationEnum $enum;

...
}
```
6) DTO with enums of `BackedEnum` property
```php
final readonly class ExampleDTO {
...
  /** @var AnApplicationEnum[] $enums */
  #[RequestProperty(property: 'enums', rules: 'required|list', listRules: AnApplicationEnum::class)]
  public array $enums;

...
}
```

## Restrictions
```diff
- Important note: for different requestDataType
```
**if DTO has nested objects or arrays of nested objects, the `requestDataType` of these entities is ignored and taken from the parrent entity**

So if you use
```php
final readonly class ExampleDTO {
...

  #[RequestProperty(property: 'child', rules: 'required|array', requestDataType: RequestProperty::BODY_TYPE)]
  public NestedDTO $child;

...
}

// NestedDTO should contain properties with attributes
final readonly class NestedDTO {
  #[RequestProperty(property: 'id', rules: 'required|integer|min:0', requestDataType: RequestProperty::QUERY_TYPE)]
  public int $id;

  #[RequestProperty(property: 'email', rules: 'required|string|email', requestDataType: RequestProperty::QUERY_TYPE)]
  public string $email;
}
```
In nested object `requestDataType` will not work and it be `RequestProperty::BODY_TYPE` like in parrent entity

```diff
- Another one: for list validation
```
**Method  `DataManager::validateAndConvertList` can only work with data from request body, so all properties of entity will be force casted to type `RequestProperty::BODY_TYPE` in this usage**

