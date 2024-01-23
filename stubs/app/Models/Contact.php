<?php

namespace App\Models;

use App\Enums\ContactBelongsTo;
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
        'belongs_to'
    ];

    protected $casts = [
        'channel' => ContactChannels::class,
        'belongs_to'=> ContactBelongsTo::class
    ];

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function addErrorIfChannelValueExists(Validator $validator, $contactId = null): void
    {
        $channelValue = $validator->getValue('channel_value');
        $belongsTo = $validator->getValue('belongs_to');
        $belongsToId = $validator->getValue('belongs_to_id');
        if ($channelValue && $belongsTo && $belongsToId) {
            $exists = self::query()
                ->where('channel_value', $channelValue)
                ->where('belongs_to', $belongsTo)
                ->where('contactable_id', $belongsToId)
                ->when($contactId, function (Builder $query, $contactId) {
                    $query->whereNot('id', $contactId);
                })
                ->exists();

            if ($exists) {
                $validator->errors()->add('channel_value', "'$channelValue' has already been taken");
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
