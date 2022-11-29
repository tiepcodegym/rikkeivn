<?php

namespace Rikkei\CallApi\Helpers;

use Gitlab\Api\AbstractApi;

class GitlabHelpers extends AbstractApi
{
    /**
     * get api with a path
     *
     * @param string $path
     * @param array $parameters
     * @return type
     */
    public function getPath($path, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        if ($parameters && count($parameters)) {
            foreach ($parameters as $key => $value) {
                $resolver->setDefined($key);
            }
        }
        return $this->get($path, $resolver->resolve($parameters));
    }

    /**
     * get self
     *
     * @param type $client
     * @return \self
     */
    public static function with($client)
    {
        return new self($client);
    }
}
