<?php

namespace App\Models;

use App\Enums\ContactChannels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Validator;
use Naveedali8086\LaravelHelpers\Rules\MobileNumberRule;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'channel',
        'channel_other',
        'channel_value',
    ];

    protected $casts = [
        'channel' => ContactChannels::class,
    ];

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function addErrorIfContactExists(Validator $validator, $attribute, $id = null): void
    {
        $attrValue = $validator->getValue($attribute);
        if ($attrValue) {
            $exists = self::query()
                ->where('channel_value', $attrValue)
                ->where('contactable_type', \App\Models\Customer::class)
                ->when($id, function (Builder $query, $id) {
                    $query->whereNot('id', $id);
                })
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    $attribute,
                    "'$attrValue' has already been taken"
                );
            }
        }
    }

    public static function channelValueAdditionalRules($channel): array
    {
        $rules = [];
        switch ($channel) {
            case ContactChannels::EMAIL->value:
                $rules[] = 'email';
                break;
            case ContactChannels::MOBILE->value:
                $rules[] = new MobileNumberRule;
                break;
        }
        return $rules;
    }

}
