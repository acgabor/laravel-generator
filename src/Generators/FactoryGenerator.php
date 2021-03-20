<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;

/**
 * Class FactoryGenerator.
 */
class FactoryGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;
    /** @var string */
    private $path;
    /** @var string */
    private $fileName;

    /**
     * FactoryGenerator constructor.
     *
     * @param CommandData $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathFactory;
        $this->fileName = $this->commandData->modelName.'Factory.php';
    }

    public function generate()
    {
        $templateData = get_template('factories.model_factory', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandObj->comment("\nFactory created: ");
        $this->commandData->commandObj->info($this->fileName);
    }

    /**
     * @param string $templateData
     *
     * @return mixed|string
     */
    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace(
            '$FIELDS$',
            implode(','.infy_nl_tab(1, 2), $this->generateFields()),
            $templateData
        );

        return $templateData;
    }

    /**
     * @return array
     */
    private function generateFields()
    {
        $fields = [];

        foreach ($this->commandData->fields as $field) {
            if ($field->isPrimary) {
                continue;
            }
            if (in_array($field->name,['created_at','updated_at','deleted_at']))
            {
                continue;
            }
            $prefix = $this->commandData->modelName.'_'.$field->name;
            $fakeableTypes = [
                'string' => '$this->faker->numerify("'.$prefix.'_####")',
                'text' => '$this->faker->numerify("'.$prefix.'_####")',
                'date' => '$this->faker->date()',
                'time' => '$this->faker->time()',
                'guid' => '$this->faker->word',
                'datetimetz' => '$this->faker->dateTime()',
                'datetime' => '$this->faker->dateTime()',
                'timestamp' => '$this->faker->dateTime()',
                'integer' => '$this->faker->randomNumber()',
                'bigint' => '$this->faker->randomNumber()',
                'smallint' => '$this->faker->randomNumber()',
                'tinyiint' => '$this->faker->numberBetween(1,5)',
                'decimal' => '$this->faker->randomNumber(8)',
                'float' => '$this->faker->randomFloat()',
                'boolean' => '$this->faker->boolean'
            ];
    
            $fakeableNames = [
                'city' => '$this->faker->city',
                'company' => '$this->faker->company',
                'country' => '$this->faker->country',
                'description' => '"'.$prefix.'_'.'".$this->faker->text(100)',
                'email' => '$this->faker->unique()->safeEmail',
                'first_name' => '$this->faker->firstName',
                'firstname' => '$this->faker->firstName',
                'guid' => '$this->faker->uuid',
                'last_name' => '$this->faker->lastName',
                'lastname' => '$this->faker->lastName',
                'lat' => '$this->faker->latitude',
                'latitude' => '$this->faker->latitude',
                'lng' => '$this->faker->longitude',
                'longitude' => '$this->faker->longitude',
                'name' => '"'.$prefix.'_'.'".$this->faker->text(10)',
                'password' => 'bcrypt($this->faker->password)',
                'phone' => '$this->faker->phoneNumber',
                'phone_number' => '$this->faker->phoneNumber',
                'postcode' => '$this->faker->postcode',
                'postal_code' => '$this->faker->postcode',
                'remember_token' => 'Str::random(10)',
                'slug' => '$this->faker->slug',
                'street' => '$this->faker->streetName',
                'address1' => '$this->faker->streetAddress',
                'address2' => '$this->faker->secondaryAddress',
                'summary' => '$this->faker->text',
                'rating_num' => '$this->faker->numberBetween(1,5)',
                'url' => '$this->faker->url',
                'user_name' => '$this->faker->userName',
                'username' => '$this->faker->userName',
                'uuid' => '$this->faker->uuid',
                'zip' => '$this->faker->postcode',
            ];
    
            if (substr($field->name, -3) == '_id')
            {
                $otherModelName = str_replace('_id','',$field->name);
                $camelCaseModelName = str_replace('_', '', ucwords($otherModelName, '_'));
                $fakerData = '\\App\\Models\\'.$camelCaseModelName.'::all()->random()->id';
            }
            elseif (isset($fakeableNames[$field->name])) {
                $fakerData = $fakeableNames[$field->name];
            }
            elseif (isset($fakeableTypes[$field->fieldType])) {
                $fakerData = $fakeableTypes[$field->fieldType];
            }
            elseif($field->fieldType == 'enum')
            {
                $fakerData = '$this->faker->randomElement('.
                            GeneratorFieldsInputUtil::prepareValuesArrayStr($field->htmlValues).
                            ')';
            }
            else
            {
                $fakerData = '"'.$prefix.'_'.'".$this->faker->word';
            }
            $fieldData = "'".$field->name."' => ".$fakerData;

            $fields[] = $fieldData;
        }

        return $fields;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Factory file deleted: '.$this->fileName);
        }
    }
}
