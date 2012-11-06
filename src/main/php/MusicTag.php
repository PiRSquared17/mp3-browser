<?php

/**
 * This file is part of mp3 Browser.
 *
 * This is free software: you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation, either version 2 of the
 * License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License (V2) along with this. If not,
 * see <http://www.gnu.org/licenses/>.
 *
 * Previous copyright likely held by others such as Jon Hollis, Luke Collymore, as associated with
 * dotcomdevelopment.com.
 * Copyright 2012 Sander Verhagen (verhagen@sander.com).
 */
// all the regular expression stuff is defined here, allowing inter-constant reuse
define("PARAM_PATTERN_PART", "\s+(\w+)=\"(.*?)\"");
define("PARAM_PATTERN", "#" . PARAM_PATTERN_PART . "#");
define("MUSIC_PATTERN", "#{music((" . PARAM_PATTERN_PART . ")*)\s*}(.*?){/music}#");
define("PARAM_PATTERN_GROUP_KEY", "1");
define("PARAM_PATTERN_GROUP_VALUE", "2");
define("MUSIC_PATTERN_GROUP_PARAMETERS", "1");
define("MUSIC_PATTERN_GROUP_PATHTRAIL", "5");

class MusicTag {

    private $originalFullTag;
    private $replacementContent;
    private $parameters = array();
    private $pathTrail;

    public function __construct($originalFullTag) {
        $this->originalFullTag = $originalFullTag;
        $this->parsePathTrail();
        $this->parseParameters();
    }

    /**
     * Get a string representation. Introduced to support array_unique comparisions
     * @return string string representation of music tag
     */
    public function __toString() {
        return $this->originalFullTag;
    }

    private function parseParameters() {
        $parametersBlock = $this->getParametersBlock();
        preg_match_all(PARAM_PATTERN, $parametersBlock, $matches, PREG_PATTERN_ORDER);

        $keys = $matches[PARAM_PATTERN_GROUP_KEY];
        $values = $matches[PARAM_PATTERN_GROUP_VALUE];
        if (count($keys)) {
            $this->parameters = array_combine($keys, $values);
        }
    }

    private function getParametersBlock() {
        preg_match_all(MUSIC_PATTERN, $this->originalFullTag, $matches, PREG_PATTERN_ORDER);
        if (count($matches[MUSIC_PATTERN_GROUP_PARAMETERS]) != 1) {
            $message = "Illegal pattern was matched, looking for block of all parameters: ";
            $message .= count($matches[MUSIC_PATTERN_GROUP_PARAMETERS]);
            throw new Exception($message);
        }
        return $matches[MUSIC_PATTERN_GROUP_PARAMETERS][0];
    }

    private function parsePathTrail() {
        preg_match_all(MUSIC_PATTERN, $this->originalFullTag, $matches, PREG_PATTERN_ORDER);
        if (count($matches[MUSIC_PATTERN_GROUP_PATHTRAIL]) != 1) {
            $message = "Illegal pattern was matched, looking for music tag count: ";
            $message .= count($matches[MUSIC_PATTERN_GROUP_PATHTRAIL]);
            throw new Exception($message);
        }
        $this->pathTrail = $matches[MUSIC_PATTERN_GROUP_PATHTRAIL][0];
    }

    public function getPathTrail() {
        return $this->pathTrail;
    }

    public function getFullTag() {
        return $this->originalFullTag;
    }

    public function setReplacementContent($replacementContent) {
        $this->replacementContent = $replacementContent;
    }

    public function getReplacementContent() {
        return $this->replacementContent;
    }

    private function getParameter($name, $alternative) {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }
        return $alternative;
    }

    public function getPageNumber() {
        return $this->getParameter("pageNumber", 0);
    }

    public function getOffset() {
        return $this->getParameter("offset", 0);
    }

}