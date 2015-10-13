<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Utils\Clearable;

abstract class IntegrationTestSearchEngineAbstract implements SearchEngine, Clearable
{
    /**
     * @return SearchDocument[]
     */
    abstract protected function getSearchDocuments();

    /**
     * @inheritdoc
     */
    final public function query($queryString, Context $queryContext, array $facetFields)
    {
        $allDocuments = $this->getSearchDocuments();
        $matchingDocuments = $this->getSearchDocumentsForQueryInContext($allDocuments, $queryString, $queryContext);

        return $this->createSearchEngineResponse($facetFields, ...array_values($matchingDocuments));
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @param string $queryString
     * @param Context $queryContext
     * @return SearchDocument[]
     */
    private function getSearchDocumentsForQueryInContext(array $searchDocuments, $queryString, Context $queryContext)
    {
        $docsMatchingContext = $this->getSearchDocumentsMatchingContext($searchDocuments, $queryContext);
        return $this->getSearchDocumentsMatchingQueryString($docsMatchingContext, $queryString);
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @param Context $context
     * @return SearchDocument[]
     */
    private function getSearchDocumentsMatchingContext(array $searchDocuments, Context $context)
    {
        return array_filter($searchDocuments, function (SearchDocument $searchDocument) use ($context) {
            return $this->hasMatchingContext($context, $searchDocument);
        });
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @param string $queryString
     * @return SearchDocument[]
     */
    private function getSearchDocumentsMatchingQueryString(array $searchDocuments, $queryString)
    {
        return array_filter($searchDocuments, function (SearchDocument $searchDocument) use ($queryString) {
            return $this->isAnyFieldValueOfSearchDocumentMatchesQueryString($searchDocument, $queryString);
        });
    }

    /**
     * @inheritdoc
     */
    final public function getSearchDocumentsMatchingCriteria(
        SearchCriteria $criteria,
        Context $context,
        array $facetFields
    ) {
        $matchingDocuments = array_filter(
            $this->getSearchDocuments(),
            function (SearchDocument $searchDocument) use ($criteria, $context) {
                return $criteria->matches($searchDocument) && $context->isSubsetOf($searchDocument->getContext());
            }
        );

        return $this->createSearchEngineResponse($facetFields, ...array_values($matchingDocuments));
    }

    /**
     * @param SearchDocument $searchDocument
     * @return string
     */
    final protected function getSearchDocumentIdentifier(SearchDocument $searchDocument)
    {
        return $searchDocument->getProductId() . ':' . $searchDocument->getContext()->toString();
    }

    /**
     * @param Context $queryContext
     * @param SearchDocument $searchDocument
     * @return bool
     */
    private function hasMatchingContext(Context $queryContext, SearchDocument $searchDocument)
    {
        $hasAtLeastOneMatchingContextPart = false;
        $documentContext = $searchDocument->getContext();
        foreach ($queryContext->getSupportedCodes() as $code) {
            if ($documentContext->supportsCode($code)) {
                if (!$this->hasMatchingContextValue($queryContext, $documentContext, $code)) {
                    return false;
                }
                $hasAtLeastOneMatchingContextPart = true;
            }
        }
        return $hasAtLeastOneMatchingContextPart;
    }

    /**
     * @param Context $queryContext
     * @param Context $documentContext
     * @param string $code
     * @return bool
     */
    private function hasMatchingContextValue(Context $queryContext, Context $documentContext, $code)
    {
        return $queryContext->getValue($code) === $documentContext->getValue($code);
    }

    /**
     * @param SearchDocument $searchDocument
     * @param string $queryString
     * @return bool
     */
    private function isAnyFieldValueOfSearchDocumentMatchesQueryString(SearchDocument $searchDocument, $queryString)
    {
        /** @var SearchDocumentField $field */
        foreach ($searchDocument->getFieldsCollection() as $field) {
            if ($this->isFieldWithMatchingValue($field, $queryString)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param SearchDocumentField $field
     * @param string $queryString
     * @return bool
     */
    private function isFieldWithMatchingValue(SearchDocumentField $field, $queryString)
    {
        foreach ($field->getValues() as $value) {
            if (stripos($value, $queryString) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string[] $facetFieldCodes
     * @param SearchDocumentCollection $documentCollection
     * @return SearchEngineFacetFieldCollection
     */
    private function createFacetFieldsCollectionFromSearchDocumentCollection(
        array $facetFieldCodes,
        SearchDocumentCollection $documentCollection
    ) {
        $attributeCounts = $this->createAttributeValueCountArrayFromSearchDocumentCollection(
            $documentCollection,
            $facetFieldCodes
        );
        $facetFields = array_map(function ($attributeCode, $attributeValues) {
            $facetFieldValues = $this->getFacetFieldValuesFromAttributeValues($attributeValues);
            return new SearchEngineFacetField(AttributeCode::fromString($attributeCode), ...$facetFieldValues);
        }, array_keys($attributeCounts), $attributeCounts);

        return new SearchEngineFacetFieldCollection(...$facetFields);
    }

    /**
     * @param SearchDocumentCollection $documentCollection
     * @param string[] $facetFieldCodes
     * @return array[]
     */
    private function createAttributeValueCountArrayFromSearchDocumentCollection(
        SearchDocumentCollection $documentCollection,
        array $facetFieldCodes
    ) {
        return array_reduce(
            $documentCollection->getDocuments(),
            function ($carry, SearchDocument $document) use ($facetFieldCodes) {
                return array_reduce(
                    $document->getFieldsCollection()->getFields(),
                    function ($carry, SearchDocumentField $searchDocumentField) use ($facetFieldCodes) {
                        if (!in_array($searchDocumentField->getKey(), $facetFieldCodes)) {
                            return $carry;
                        }
                        return array_reduce($searchDocumentField->getValues(),
                            function ($carry, $value) use ($searchDocumentField) {
                                if (!isset($carry[$searchDocumentField->getKey()][$value])) {
                                    $carry[$searchDocumentField->getKey()][$value] = 0;
                                }
                                $carry[$searchDocumentField->getKey()][$value] ++;

                                return $carry;
                            },
                            $carry
                        );
                    },
                    $carry
                );
            },
            []
        );
    }

    /**
     * @param string[] $facetFields
     * @param SearchDocument ...$searchDocuments
     * @return SearchEngineResponse
     */
    private function createSearchEngineResponse(array $facetFields, SearchDocument ...$searchDocuments)
    {
        $documentCollection = new SearchDocumentCollection(...$searchDocuments);
        $facetFieldCollection = $this->createFacetFieldsCollectionFromSearchDocumentCollection(
            $facetFields,
            $documentCollection
        );

        return new SearchEngineResponse($documentCollection, $facetFieldCollection);
    }

    /**
     * @param int[] $attributeValues
     * @return SearchEngineFacetFieldValueCount[]
     */
    private function getFacetFieldValuesFromAttributeValues($attributeValues)
    {
        return array_map(function ($value, $count) {
            return SearchEngineFacetFieldValueCount::create((string)$value, $count);
        }, array_keys($attributeValues), $attributeValues);
    }
}
