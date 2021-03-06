<?php

namespace Tanthammar\TallForms\Traits;


use Illuminate\Support\Arr;

trait Helpers
{
    //TODO expand method to include keyval and array fields
    //you cannot get rules for keyval or array fields with this method
    protected function getFieldValueByKey(string $fieldKey, string $fieldValue)
    {

        $fieldName = \Str::replaceFirst('form_data.', '', $fieldKey);
        $fieldsArray = $this->fieldsToArray();
        $field = collect($fieldsArray)->firstWhere('name', $fieldName) ?? collect($fieldsArray)->firstWhere('key', $fieldKey);
//        $field = Arr::first($fieldsArray, (fn($value) => $value['name'] === $fieldName)) ?? Arr::first($fieldsArray, (fn($value) => $value['key'] === $fieldKey));
        return optional($field)[$fieldValue];
    }

    protected function getFieldType(string $fieldKey)
    {
        return $this->getFieldValueByKey($fieldKey, 'type');
    }

    //Does not convert Array or KeyVal fields, they remain as objects!!
    protected function fieldsToArray(): array
    {
        $array = [];
        foreach ($this->fields() as $field) {
            if (filled($field)) $array[] = $field->fieldToArray(); //in BaseField and IsArrayField
        }
        return $array;
    }

    protected function fieldNames(): array
    {
        return $fieldNames = collect($this->fields())->map(function ($field) {
            return filled($field) ? $field->name : null;
        })->toArray();
    }


    /**
     * Executes before field validation, creds to "@roni", livewire discord channel member
     * @param string $field
     * @param string $hook
     * @return string
     */
    protected function parseFunctionNameFrom(string $field, $hook = 'updated'): string
    {
        return $hook . \Str::of($field)->replace('.', '_')->studly()->replaceFirst('FormData', '');
    }



    public function tallFillField($array)
    {
        $this->form_data[$array['field']] = $array['value'];
    }

    // All other methods regarding tags are in Tanthammar\TallForms\SpatieTags
    // It's intended to be called in the onCreateModel() method, to sync tags after the model is created
    public function syncTags($field, $tagType = null)
    {
        $tags = data_get($this->custom_data, $field);
        if (filled($tags = explode(",", $tags)) && optional($this->model)->exists) {
            filled($tagType) ? $this->model->syncTagsWithType($tags, $tagType) : $this->model->syncTags($tags);
        }
    }

    // in blade views to strip "form data" from field validation
    public function errorMessage($message, $key='', $label='')
    {
        $return = str_replace('form_data.', '', $message);
        return str_replace('form data.', '', $return);
//        return \Str::replaceFirst('form data.', '', $message);
    }

    public static function unique_words(string $scentence): string
    {
        return implode(' ',array_unique(explode(' ', $scentence)));
    }
}
