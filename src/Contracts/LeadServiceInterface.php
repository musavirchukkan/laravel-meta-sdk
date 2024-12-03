<?php

namespace YourVendor\MetaSdk\Contracts;

interface LeadServiceInterface
{
    public function getFormLeads(string $formId, string $pageToken): array;
    public function getLeadForms(string $pageId, string $pageToken): array;
    public function getLeadFormDetails(string $formId, string $pageToken): array;
}