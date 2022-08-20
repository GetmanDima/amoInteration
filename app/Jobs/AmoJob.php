<?php

namespace App\Jobs;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Filters\CompaniesFilter;
use AmoCRM\Filters\ContactsFilter;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\BaseCustomFieldValuesModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\Leads\LossReasons\LossReasonModel;
use AmoCRM\Models\TagModel;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\Lead;
use App\Models\LossReason;
use App\Models\Tag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JetBrains\PhpStorm\NoReturn;

class AmoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $amoCredentials;
    private int $userId;
    private AmoCRMApiClient $amoApiClient;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $amoCredentials, $userId)
    {
        $this->userId = $userId;
        $this->amoCredentials = $amoCredentials;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->amoApiClient = $this->getAmoApiClient();
        $this->wipeData();

        try {
            $leads = $this->getLeads();
            $companies = $this->getCompanies($leads);
            $contacts = $this->getContacts($leads);
        } catch (AmoCRMMissedTokenException | AmoCRMoAuthApiException | AmoCRMApiException$e) {
            dd($e);
        }

        $lossReasons = $this->getLossReasons($leads);
        $customFieldsValues = $this->getCustomFieldsValues($leads);
        $tags = $this->getTags($leads);

        $this->insertCompanies($companies);
        $this->insertContacts($contacts);
        $this->insertLossReasons($lossReasons);
        $this->insertCustomFields($customFieldsValues);
        $this->insertTags($tags);
        $this->insertLeads($leads);

        echo PHP_EOL . 'Data updated' . PHP_EOL;
    }

    private function getAmoApiClient(): AmoCRMApiClient
    {
        $amoCredentials = $this->amoCredentials;

        $apiClient = new AmoCRMApiClient($amoCredentials['clientId'], $amoCredentials['clientSecret'], null);
        $apiClient->setAccessToken($amoCredentials['accessToken']);
        $apiClient->setAccountBaseDomain($amoCredentials['baseDomain']);

        return $apiClient;
    }

    private function wipeData() {
        Lead::where('amoUserId', $this->userId)->delete();
        Contact::where('amoUserId', $this->userId)->delete();
        CustomField::where('amoUserId', $this->userId)->delete();
        Tag::where('amoUserId', $this->userId)->delete();
        Company::where('amoUserId', $this->userId)->delete();
        LossReason::where('amoUserId', $this->userId)->delete();
    }

    /**
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws AmoCRMMissedTokenException
     */
    private function getLeads(): array
    {
        $amoApiClient = $this->amoApiClient;

        $leadsWith = [
            LeadModel::CATALOG_ELEMENTS,
            LeadModel::IS_PRICE_BY_ROBOT,
            LeadModel::LOSS_REASON,
            LeadModel::CONTACTS,
            LeadModel::SOURCE_ID,
        ];

        return $amoApiClient->leads()->get(with: $leadsWith)->all();
    }

    /**
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws AmoCRMMissedTokenException
     */
    private function getCompanies($leads): array
    {
        $ids = [];

        /** @var LeadModel $lead */
        foreach ($leads as $lead) {
            $company = $lead->getCompany();

            if (!is_null($company)) {
                $ids[] = $company->getId();
            }
        }

        $ids = array_unique($ids);

        $filter = new CompaniesFilter();
        $filter->setIds($ids);

        return $this->amoApiClient->companies()->get($filter)->all();
    }

    /**
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws AmoCRMMissedTokenException
     */
    private function getContacts($leads): array
    {
        $ids = [];

        /** @var LeadModel $lead */
        foreach ($leads as $lead) {
            $contacts = $lead->getContacts();

            if (!is_null($contacts)) {
                foreach ($contacts as $contact) {
                    $ids[] = $contact->getId();
                }
            }
        }

        $ids = array_unique($ids);

        $contactFilter = new ContactsFilter();
        $contactFilter->setIds($ids);

        return $this->amoApiClient->contacts()->get()->all();
    }

    private function getLossReasons($leads): array
    {
        $lossReasons = [];

        /** @var LeadModel $lead */
        foreach ($leads as $lead) {
            $lossReason = $lead->getLossReason();

            if (!is_null($lossReason)) {
                $lossReasons[] = $lossReason;
            }
        }

        return $this->getUniqueModels($lossReasons);
    }

    private function getCustomFieldsValues($leads): array
    {
        $fieldsValues = [];

        /** @var LeadModel $lead */
        foreach ($leads as $lead) {
            $leadFieldsValues = $lead->getCustomFieldsValues();

            if (!is_null($leadFieldsValues)) {
                foreach ($leadFieldsValues as $fieldValues) {
                    $fieldsValues[] = $fieldValues;
                }
            }
        }

        return $this->getUniqueModels($fieldsValues, 'fieldId');
    }

    private function getTags($leads): array
    {
        $tags = [];

        /** @var LeadModel $lead */
        foreach ($leads as $lead) {
            $leadTags = $lead->getTags();

            if (!is_null($leadTags)) {
                foreach ($leadTags as $tag) {
                    $tags[] = $tag;
                }
            }
        }

        return $this->getUniqueModels($tags);
    }

    public function insertCompanies($companies)
    {
        $data = [];

        /** @var CompanyModel $company */
        foreach ($companies as $company) {
            $data[] = [
                'amoId' => $company->getId(),
                'name' => $company->getName(),
                'responsibleUserId' => $company->getResponsibleUserId(),
                'groupId' => $company->getGroupId(),
                'createdBy' => $company->getCreatedBy(),
                'updatedBy' => $company->getUpdatedBy(),
                'createdAt' => $company->getCreatedAt(),
                'updatedAt' => $company->getUpdatedAt(),
                'closestTaskAt' => $company->getClosestTaskAt(),
                'accountId' => $company->getAccountId(),
                'amoUserId' => $this->userId
            ];
        }

        Company::insert($data);
    }

    public function insertContacts($contacts)
    {
        /** @var ContactModel $contact */
        foreach ($contacts as $contact) {
            $data[] = [
                'amoId' => $contact->getId(),
                'name' => $contact->getName(),
                'firstname' => $contact->getFirstName(),
                'lastname' => $contact->getLastName(),
                'responsibleUserId' => $contact->getResponsibleUserId(),
                'groupId' => $contact->getGroupId(),
                'createdBy' => $contact->getCreatedBy(),
                'updatedBy' => $contact->getUpdatedBy(),
                'createdAt' => $contact->getCreatedAt(),
                'updatedAt' => $contact->getUpdatedAt(),
                'closestTaskAt' => $contact->getClosestTaskAt(),
                'accountId' => $contact->getAccountId(),
                'isMain' => $contact->getIsMain(),
                'amoUserId' => $this->userId
            ];
        }

        Contact::insert($data);
    }

    public function insertLossReasons($lossReasons)
    {
        $data = [];

        /** @var LossReasonModel $lossReason */
        foreach ($lossReasons as $lossReason) {
            if (!is_null($lossReason)) {
                $data[] = [
                    'amoId' => $lossReason->getId(),
                    'name' => $lossReason->getName(),
                    'sort' => $lossReason->getSort(),
                    'createdAt' => $lossReason->getCreatedAt(),
                    'updatedAt' => $lossReason->getUpdatedAt(),
                    'amoUserId' => $this->userId
                ];
            }
        }

        LossReason::insert($data);
    }

    public function insertCustomFields($customFieldsValues)
    {
        $data = [];

        /** @var BaseCustomFieldValuesModel $field */
        foreach ($customFieldsValues as $field) {
            $data[] = [
                'amoId' => $field->getFieldId(),
                'code' => $field->getFieldCode(),
                'name' => $field->getFieldName(),
                'type' => $field->getFieldType(),
                'amoUserId' => $this->userId
            ];
        }

        CustomField::insert($data);
    }

    public function insertTags($tags)
    {
        $data = [];

        /** @var TagModel $tag */
        foreach ($tags as $tag) {
            $data[] = [
                'amoId' => $tag->getid(),
                'name' => $tag->getName(),
                'color' => $tag->getColor(),
                'requestId' => $tag->getRequestId(),
                'amoUserId' => $this->userId
            ];
        }

        Tag::insert($data);
    }

    #[NoReturn] private function insertLeads($leads) {
        /** @var LeadModel $amoLead */
        foreach ($leads as $amoLead) {
            $data = [
                'amoId' => $amoLead->getId(),
                'name' => $amoLead->getName(),
                'responsibleUserId' => $amoLead->getResponsibleUserId(),
                'groupId' => $amoLead->getGroupId(),
                'createdBy' => $amoLead->getCreatedBy(),
                'updatedBy' => $amoLead->getUpdatedBy(),
                'createdAt' => $amoLead->getCreatedAt(),
                'updatedAt' => $amoLead->getUpdatedAt(),
                'accountId' => $amoLead->getAccountId(),
                'pipelineId' => $amoLead->getPipelineId(),
                'statusId' => $amoLead->getStatusId(),
                'closedAt' => $amoLead->getClosedAt(),
                'closestTaskAt' => $amoLead->getClosestTaskAt(),
                'price' => $amoLead->getPrice(),
                'lossReasonId' => $amoLead->getLossReasonId(),
                'isDeleted' => $amoLead->getIsDeleted(),
                'sourceId' => $amoLead->getSourceId(),
                'sourceExternalId' => $amoLead->getSourceExternalId(),
                'score' => $amoLead->getScore(),
                'isPriceModifiedByRobot' => $amoLead->getIsPriceModifiedByRobot(),
                'companyId' => is_null($amoLead->getCompany()) ? null : $amoLead->getCompany()->getId(),
                'visitorUid' => $amoLead->getVisitorUid(),
                'amoUserId' => $this->userId
            ];

            $lead = Lead::create($data);
            $contacts = $amoLead->getContacts();

            if (!is_null($contacts)) {
                $this->attachContactsToLead($lead, $contacts);
            }

            $customFieldValues = $amoLead->getCustomFieldsValues();

            if (!is_null($customFieldValues)) {
                $this->attachCustomFieldsValuesToLead($lead, $customFieldValues);
            }

            $tags = $amoLead->getTags();

            if (!is_null($tags)) {
                $this->attachTagsToLead($lead, $tags);
            }
        }
    }

    private function attachTagsToLead(Lead $lead, $tags) {
        $tagIds = [];

        /** @var TagModel $tag */
        foreach ($tags as $tag) {
            $tagIds[] = $tag->getId();
        }

        $lead->tags()->attach($tagIds);
    }

    private function attachCustomFieldsValuesToLead(Lead $lead, $customFieldValues) {
        /** @var BaseCustomFieldValuesModel $field */
        foreach ($customFieldValues as $field) {
            $id = $field->getFieldId();
            $values = json_encode($field->getValues()->jsonSerialize());
            $lead->customFields()->attach($id, ['values' => $values]);
        }
    }

    private function attachContactsToLead($lead, $contacts) {
        $contactIds = [];

        foreach ($contacts as $contact) {
            $contactIds[] = $contact->getId();
        }

        $lead->contacts()->attach($contactIds);
    }

    private function getUniqueModels($models, $field = 'id'): array
    {
        $fieldGetter = 'get' . ucfirst($field);
        $ids = [];
        $uniqueModels = [];

        foreach ($models as $model) {
            $id = $model->$fieldGetter();

            if (!in_array($id, $ids)) {
                $uniqueModels[] = $model;
                $ids[] = $id;
            }
        }

        return $uniqueModels;
    }
}
