<?php

namespace Bigfork\SilverstripeWebpFormatter\Extensions;

use Bigfork\SilverstripeWebpFormatter\FilenameParsing\HashFileIDHelper;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Core\Extension;

class ImageExtension extends Extension
{
    public function Webp(): ?AssetContainer
    {
        $pathParts = pathinfo($this->owner->getFilename());
        $variant = $this->owner->variantName(HashFileIDHelper::getExtensionRewriteVariant(), $pathParts['extension'], 'webp');
        return $this->owner->manipulateImage($variant, fn(Image_Backend $backend) => $backend);
    }
}
