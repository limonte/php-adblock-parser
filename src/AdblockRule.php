<?php
namespace Limonte;

class AdblockRule
{
    private $rule;

    private $regex;

    private $isComment = false;

    private $isHtml = false;

    private $isException = false;

    public function __construct($rule)
    {
        $this->rule = $rule;

        if (Str::startsWith($this->rule, '@@')) {
            $this->isException = true;
            $this->rule = mb_substr($this->rule, 2);
        }

        // comment
        if (Str::startsWith($rule, '!') || Str::startsWith($rule, '[Adblock')) {
            $this->isComment = true;

        // HTML rule
        } elseif (Str::contains($rule, '##') || Str::contains($rule, '#@#')) {
            $this->isHtml = true;

        // URI rule
        } else {
            $this->makeRegex();
        }
    }

    /**
     * @param  string  $url
     *
     * @return  boolean
     */
    public function matchUrl($url)
    {
        return (boolean)preg_match(
            '/' . $this->getRegex() . '/',
            $url
        );
    }

    /**
     * @return  string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @return  boolean
     */
    public function isComment()
    {
        return $this->isComment;
    }

    /**
     * @return  boolean
     */
    public function isHtml()
    {
        return $this->isHtml;
    }

    /**
     * @return  boolean
     */
    public function isException()
    {
        return $this->isException;
    }

    private function makeRegex()
    {
        if (empty($this->rule)) {
            throw new InvalidRuleException("Empty rule");
        }

        $regex = $this->rule;

        // Check if the rule isn't already regexp
        if (Str::startsWith($regex, '/') && Str::endsWith($regex, '/')) {
            $this->regex = mb_substr($this->rule, 1, mb_strlen($this->rule) - 2);

            if (empty($this->regex)) {
                throw new InvalidRuleException("Empty rule");
            }

            return;
        }

        // escape special regex characters
        $regex = preg_replace("/([\\\.\$\+\?\{\}\(\)\[\]\/])/", "\\\\$1", $this->rule);

        // Separator character ^ matches anything but a letter, a digit, or
        // one of the following: _ - . %. The end of the address is also
        // accepted as separator.
        $regex = str_replace("^", "([^\w\d_\-\.%]|$)", $regex);

        // * symbol
        $regex = str_replace("*", ".*", $regex);

        // | in the end means the end of the address
        if (Str::endsWith($regex, '|')) {
            $regex = mb_substr($regex, 0, mb_strlen($regex) - 1) . '$';
        }

        // || in the beginning means beginning of the domain name
        if (Str::startsWith($regex, '||')) {
            if (mb_strlen($regex) > 2) {
                // http://tools.ietf.org/html/rfc3986#appendix-B
                $regex = "^([^:\/?#]+:)?(\/\/([^\/?#]*\.)?)?" . mb_substr($regex, 2);
            }
        // | in the beginning means start of the address
        } elseif (Str::startsWith($regex, '|')) {
            $regex = '^' . mb_substr($regex, 1);
        }

        // other | symbols should be escaped
        $regex = preg_replace("/\|(?![\$])/", "\|$1", $regex);

        $this->regex = $regex;
    }
}
