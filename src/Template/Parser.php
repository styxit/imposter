<?php

namespace Spoof\Template;

class Parser
{
    /**
     * @var string The template string that will be used as the.
     */
    private $template;

    /**
     * Parser constructor.
     *
     * @param $templateName The file to use as template.
     *
     * @throws \Exception When the template file is not found.
     */
    public function __construct(string $templateName)
    {
        $filePath = __DIR__.'/../../templates/'.$templateName.'.json';

        if (!file_exists($filePath)) {
            throw new \Exception('Template source file not found');
        }
        $this->template = file_get_contents($filePath);
    }

    /**
     * Parse the template with all the provided options.
     *
     * @param string[] $options The variables, to insert in the template. Array keys are ignored.
     *
     * @return string Parsed template.
     */
    public function parse(array $options): string
    {
        /*
         * Create array with all options to pass to sprintf() method.
         * Start with all the replacement values.
         */
        $sprinfOptions = array_values($options);

        // Prepend the template string (that contains the %s and %d parts).
        array_unshift($sprinfOptions, $this->template);

        // Call sprintf() method to set the values in the template.
        return call_user_func_array('sprintf', $sprinfOptions);
    }
}
