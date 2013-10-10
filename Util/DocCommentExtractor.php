<?php

namespace Nelmio\ApiDocBundle\Util;

class DocCommentExtractor
{
    /**
     * @param  \Reflector $reflected
     * @return string
     */
    public function getDocComment(\Reflector $reflected)
    {
        $comment = $reflected->getDocComment();

        // let's clean the doc block
        $comment = str_replace('/**', '', $comment);
        $comment = str_replace('*', '', $comment);
        $comment = str_replace('*/', '', $comment);
        $comment = str_replace("\r", '', trim($comment));
        $comment = preg_replace("#^\n[ \t]+[*]?#i", "\n", trim($comment));
        $comment = preg_replace("#[\t ]+#i", ' ', trim($comment));
        $comment = str_replace("\"", "\\\"", $comment);

        return $comment;
    }

    /**
     * @param  \Reflector $reflected
     * @return string
     */
    public function getDocCommentText(\Reflector $reflected)
    {
        $comment = $reflected->getDocComment();

        // Remove PHPDoc
        $comment = preg_replace('/^\s+\* @[\w0-9]+.*/msi', '', $comment);

        // let's clean the doc block
        $comment = str_replace('/**', '', $comment);
        $comment = str_replace('*/', '', $comment);
        $comment = preg_replace('/^\s*\* ?/m', '', $comment);

        return trim($comment);
    }

}
