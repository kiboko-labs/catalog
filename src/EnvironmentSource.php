<?php


namespace Brera;


class EnvironmentSource
{
    /**
     * @var array
     */
    private $environmentMatrix;

    /**
     * @var EnvironmentBuilder
     */
    private $environmentBuilder;

    public function __construct(array $environmentMatrix, EnvironmentBuilder $environmentBuilder)
    {
        $this->environmentMatrix = $environmentMatrix;
        $this->environmentBuilder = $environmentBuilder;
    }

    /**
     * @return string[]
     */
    public function getEnvironmentPartKeys()
    {
        return array_keys($this->environmentMatrix);
    }

    /**
     * @param string $part
     * @return array
     */
    public function getEnvironmentValuesForPart($part)
    {
        if (!array_key_exists($part, $this->environmentMatrix)) {
            return [];
        }
        return $this->environmentMatrix[$part];
    }

    /**
     * @param array $partsToExtract
     * @return array
     */
    private function extractCartesianProductOfEnvironmentsAsArray(array $partsToExtract)
    {
        if (!$this->ifVersionIsASpecifiedPart($partsToExtract)) {
            $partsToExtract[] = VersionedEnvironment::KEY;
        }
        //return $this->getAllPossibleCombinations($partsToExtract);
        return $this->getAllPossibleCombinationsRecursively($partsToExtract);
    }

    /**
     * @param array $partsToExtract
     * @return bool
     */
    private function ifVersionIsASpecifiedPart(array $partsToExtract)
    {
        return in_array(VersionedEnvironment::KEY, $partsToExtract);
    }

    /**
     * @param array $partsToExtract
     * @return array
     */
    private function getEnvironmentsThatAreRequestedAndExistInTheSource(array $partsToExtract)
    {
        return array_intersect_key($this->environmentMatrix, array_flip($partsToExtract));
    }

    /**
     * @param $presentEnvironments
     * @return mixed
     */
    private function getNumberOfSpecificEnvironments($presentEnvironments)
    {
        return array_reduce($presentEnvironments, function ($result, $parts) {
            return $result * count($parts);
        }, 1);
    }

    /**
     * @param array $partsToExtract
     * @return array
     */
    private function getAllPossibleCombinationsRecursively(array $partsToExtract)
    {
        $presentEnvironments = $this->getEnvironmentsThatAreRequestedAndExistInTheSource($partsToExtract);
        return $this->buildRecursively($presentEnvironments);
    }

    /**
     * Thanks Jonathan H. Wage for https://gist.github.com/jwage/11193216
     * 
     * @param array $set
     * @return array
     */
    private function buildRecursively(array $set)
    {
        if (!$set) {
            return array(array());
        }
        $key = key($set);
        $subset = array_shift($set);
        $cartesianSubset = $this->buildRecursively($set);
        $result = array();
        foreach ($subset as $value) {
            foreach ($cartesianSubset as $p) {
                $p[$key] = $value;
                $result[] = $p;
            }
        }
        return $result;
    }

    /**
     * @param array $partsToExtract
     * @return array
     */
    public function extractEnvironments(array $partsToExtract)
    {
        $variations = $this->extractCartesianProductOfEnvironmentsAsArray($partsToExtract);
        return $this->environmentBuilder->getEnvironments($variations);

    }
}
