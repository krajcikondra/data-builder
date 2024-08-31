# krajcik/data-builder

Package helps to write test data for tests using data builders which package generate specifically for your project.
By default, are all data filled randomly with option change one or more properties.

## Installation

```
composer require krajcik/data-builder
```

## Using

### Configuration and generate

```
    use \Krajcik\DataBuilder\Dto\Configuration;
    
    $targetFolder = '../tests/generated'; // specify some path in your project where you want generate builders

    $configuration = new Configuration(
        $targetFolder,
        'localhost',
        'myTestDb',
        'root',
        'root',
    );
```

### Define builders which want generate
Define all doctrine entities for which you want generate builder
```
    use \Krajcik\DataBuilder\Dto\BuilderToGenerateDto;

    $builderToGenerate = [
        BuilderToGenerateDto::createFromDoctrineEntity('Api\Contact\Entity\Contact'),
        BuilderToGenerateDto::createFromDoctrineEntity('Api\Contact\Entity\ContactData'),
    ];

```

### Generate builders
```
    use \Krajcik\DataBuilder\BuilderCompiler;

    $compiler = new BuilderCompiler($configuration);
    $compiler->compile($builderToGenerate);
```

### Using builders

```
    $builderFactory = new \Tests\Generated\Builder\BuilderFactory($entityManager);

    $contactData = $builderFactory->createContactDataBuilder()
        ->withFirstName('John')
        ->withLastName('Doe')
        ->buildAndSave();
    // $contactData is saved entity (Api\Contact\Entity\ContactData) from your project which you can use now in your test case

```
