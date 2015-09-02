<?php

namespace Craft;

abstract class Sitemap_BaseModel extends BaseModel
{
    /**
     * Throws exceptions for validation errors when in devMode.
     *
     * @return bool
     */
    public function validate($attributes = null, $clearErrors = true)
    {
        $validate = parent::validate($attributes, $clearErrors);

        if (!$validate && craft()->config->get('devMode')) {
            foreach ($this->getAllErrors() as $attribute => $error) {
                throw new Exception(Craft::t($error));
            }
        }

        return $validate;
    }
}
