<?php

namespace Bigfork\SilverstripeWebpFormatter\FilenameParsing;

use InvalidArgumentException;
use SilverStripe\Assets\FilenameParsing\HashFileIDHelper as SilverstripeHashFileIDHelper;
use SilverStripe\Assets\FilenameParsing\ParsedFileID;

class HashFileIDHelper extends SilverstripeHashFileIDHelper
{
    use AlternativeFileExtensionTrait;

    public function buildFileID($filename, $hash = null, $variant = null, $cleanfilename = true): string
    {
        if ($filename instanceof ParsedFileID) {
            $hash =  $filename->getHash();
            $variant =  $filename->getVariant();
            $filename =  $filename->getFilename();
        }

        if (empty($hash)) {
            throw new InvalidArgumentException('HashFileIDHelper::buildFileID requires an $hash value.');
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

        $fileID = $this->truncate($hash) . '/' . $name;

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
        $pattern = '#^(?<folder>([^/]+/)*)(?<hash>[a-f0-9]{10})/(?<basename>((?<!__)[^/.])+)(__(?<variant>[^.]+))?(?<extension>(\..+)*)$#';

        // not a valid file (or not a part of the filesystem)
        if (!preg_match($pattern, $fileID ?? '', $matches)) {
            return null;
        }

        $filename = $matches['folder'] . $matches['basename'] . $matches['extension'];
        $variant = $matches['variant'] ?? '';

        if (isset($variant)) {
            $filename = $this->restoreOriginalExtension($filename, $variant);
        }

        return new ParsedFileID($filename, $matches['hash'], $variant, $fileID);
    }

    private function truncate($hash): string
    {
        return substr($hash ?? '', 0, self::HASH_TRUNCATE_LENGTH);
    }
}
