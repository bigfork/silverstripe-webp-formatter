<?php

namespace Bigfork\SilverstripeWebpFormatter\FilenameParsing;

use SilverStripe\Assets\FilenameParsing\NaturalFileIDHelper as SilverstripeNaturalFileIDHelper;
use SilverStripe\Assets\FilenameParsing\ParsedFileID;

class NaturalFileIDHelper extends SilverstripeNaturalFileIDHelper
{
    use AlternativeFileExtensionTrait;

    public function buildFileID($filename, $hash = null, $variant = null, $cleanfilename = true): string
    {
        if ($filename instanceof ParsedFileID) {
            $hash =  $filename->getHash();
            $variant =  $filename->getVariant();
            $filename =  $filename->getFilename();
        }

        // Since we use double underscore to delimit variants, eradicate them from filename
        if ($cleanfilename) {
            $filename = $this->cleanFilename($filename);
        }

        if ($variant) {
            $filename = $this->rewriteVariantExtension($filename, $variant);
        }

        $name = basename($filename ?? '');

        // Split extension
        $extension = null;
        if (($pos = strpos($name ?? '', '.')) !== false) {
            $extension = substr($name ?? '', $pos ?? 0);
            $name = substr($name ?? '', 0, $pos);
        }

        $fileID = $name;

        // Add directory
        $dirname = ltrim(dirname($filename ?? ''), '.');
        if ($dirname) {
            $fileID = $dirname . '/' . $fileID;
        }

        // Add variant
        if ($variant) {
            $fileID .= '__' . $variant;
        }

        // Add extension
        if ($extension) {
            $fileID .= $extension;
        }

        return $fileID;
    }

    public function parseFileID($fileID): ?ParsedFileID
    {
        $pattern = '#^(?<folder>([^/]+/)*)(?<basename>((?<!__)[^/.])+)(__(?<variant>[^.]+))?(?<extension>(\..+)*)$#';

        // not a valid file (or not a part of the filesystem)
        if (!preg_match($pattern, $fileID ?? '', $matches) || str_contains($matches['folder'] ?? '', '_resampled')) {
            return null;
        }

        $filename = $matches['folder'] . $matches['basename'] . $matches['extension'];
        $variant = $matches['variant'] ?? '';

        if (isset($variant)) {
            $filename = $this->restoreOriginalExtension($filename, $variant);
        }

        return new ParsedFileID($filename, '', $variant, $fileID);
    }
}
