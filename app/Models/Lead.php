<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 */
class Lead extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'amoId', 'name', 'responsibleUserId',
        'groupId', 'createdBy', 'updatedBy',
        'createdAt', 'updatedAt', 'accountId',
        'pipelineId', 'statusId', 'closedAt',
        'closestTaskAt', 'price', 'lossReasonId',
        'isDeleted', 'sourceId', 'sourceExternalId',
        'score', 'isPriceModifiedByRobot', 'companyId',
        'visitorUid', 'amoUserId'
    ];

    public function contacts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'leads_contacts', 'leadId', 'contactId', 'amoId', 'amoId');
    }

    public function customFields(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(CustomField::class, 'leads_custom_fields', 'leadId', 'customFieldId', 'amoId', 'amoId');
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'leads_tags', 'leadId', 'tagId', 'amoId', 'amoId');
    }

    public function lossReason(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LossReason::class, 'lossReasonId', 'amoId');
    }
}
