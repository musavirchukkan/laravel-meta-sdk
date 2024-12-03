<?php

namespace YourVendor\MetaSdk\Services;

use YourVendor\MetaSdk\Contracts\LeadServiceInterface;
use YourVendor\MetaSdk\Traits\{HasMetaApiOperations, HandlesPagination};

class LeadService extends AbstractMetaService implements LeadServiceInterface
{
    use HasMetaApiOperations, HandlesPagination;

    protected array $cacheDuration = [
        'leads' => 5,      // 5 minutes for leads
        'forms' => 60,     // 1 hour for forms
        'details' => 120   // 2 hours for form details
    ];

    public function getFormLeads(string $formId, string $pageToken, int $limit = null): array
    {
        return $this->getPagedResults($formId . '/leads', [
            'fields' => 'created_time,field_data,campaign_name,platform,id'
        ], $pageToken, $limit, $this->cacheDuration['leads']);
    }

    public function getAllFormLeads(string $formId, string $pageToken): array
    {
        return $this->getPaginatedResults($formId . '/leads', [
            'fields' => 'created_time,field_data,campaign_name,platform,id'
        ], $pageToken, $this->cacheDuration['leads']);
    }

    public function getLeadForms(string $pageId, string $pageToken): array
    {
        return $this->getPagedResults($pageId . '/leadgen_forms', [
            'fields' => 'id,name,status,leads_count'
        ], $pageToken, null, $this->cacheDuration['forms']);
    }

    public function getLeadFormDetails(string $formId, string $pageToken): array
    {
        return $this->getObject($formId, $pageToken, [
            'name', 'status', 'leads_count', 'questions'
        ], $this->cacheDuration['details']);
    }

    public function clearLeadsCache(string $formId): void
    {
        $this->forget($formId . '/leads', []);
    }
}