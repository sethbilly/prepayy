<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BrandStyle extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['style', 'institutable_id', 'institutable_type'];

    /**
     * @return MorphTo
     */
    public function institutable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param $val
     */
    public function setStyleAttribute($val)
    {
        $this->attributes['style'] = e($val);
    }

    /**
     * @return mixed
     */
    public function getStyleAttribute()
    {
        return !empty($this->attributes['style']) ? html_entity_decode($this->attributes['style']) : '';
    }

    /**
     * @return string
     */
    public function getBrandStylesheet()
    {
        return '<style type="text/css">' . $this->style . '</style>';
    }

    /**
     * Returns the default brand style as a css embedded stylesheet
     * @param string $key
     * @return string
     */
    public static function getDefaultStylesheet($key = 'partner'): string
    {
        $styleConfig = self::getDefaultStyle($key);

        return '<style type="text/css">' . $styleConfig['style'] . '</style>';
    }

    /**
     * Returns the default brand style object
     * @param string $key
     * @return BrandStyle
     */
    public static function getDefaultStyle($key = 'partner'): BrandStyle
    {
        return new self(['style' => config('brand_style.' . $key)]);
    }
}
