<?php

namespace Bigfork\SilverstripeWebpFormatter;

use BadMethodCallException;
use Intervention\Image\Exception\NotSupportedException;
use SilverStripe\Assets\InterventionBackend as SilverstripeInterventionBackend;
use SilverStripe\Assets\Storage\AssetStore;

class InterventionBackend extends SilverstripeInterventionBackend
{
    public function writeToStore(AssetStore $assetStore, $filename, $hash = null, $variant = null, $config = []): ?array
    {
        try {
            $resource = $this->getImageResource();
            if (!$resource) {
                throw new BadMethodCallException("Cannot write corrupt file to store");
            }

            // Save file
            $url = $assetStore->getAsURL($filename, $hash, $variant, false);
            $extension = pathinfo($url ?? '', PATHINFO_EXTENSION);
            $result = $assetStore->setFromString(
                $resource->encode($extension, $this->getQuality())->getEncoded(),
                $filename,
                $hash,
                $variant,
                $config
            );

            // Warm cache for the result
            if ($result) {
                $this->warmCache($result['Hash'], $result['Variant']);
            }

            return $result;
        } catch (NotSupportedException) {
            return null;
        }
    }
}
