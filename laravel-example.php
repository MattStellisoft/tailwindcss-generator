<?php

class TailwindGenerator {

    public $classes;
    public $activeClasses;
    public $categorizedClasses;
    public $proseClasses;
    public $breakpoints;
    public $rules;
    public $css;
    

    public function __construct() {
        $this->classes = [];
        $this->classStyles = [];
        $this->proseClasses = [];
        $this->rules = [];
        $this->breakpoints = ['sm:', 'md:', 'lg:', 'xl:', '2xl:'];
        $this->categorizedClasses = ['nobreakpoint' => [], 'sm:' => [], 'md:' => [], 'lg:' => [], 'xl:' => []];
        $this->css = '';
    }

    public function generateTailwind($classes) {
        /**
         * Build two arrays: 
         * - One containing a lookup for all classes minus any prefixes ($this->classes), we'll use this to fetch classes from the database.
         * - A second array ($this->categorizedClasses) categorizes the classes using their breakpoint prefix or 'nobreakpoint' 
         *  should they not have a breakpoint prefix.
         */
        foreach($classes as $class) {
            if (in_array(substr($class, 0, 3), $this->breakpoints)) {
                array_push($this->classes, substr($class, 3));
                $this->categorizedClasses[substr($class, 0, 3)][$class] = null;
            } else {
                array_push($this->classes, $class);
                $this->categorizedClasses['nobreakpoint'][$class] = null;
            }
        }
        $classes = TailwindClasses::where('category', '')->whereIn('name', $this->classes)->get();
        foreach($classes as $class) {
            //Build an array containing rules
            if (!empty($class['rule'])) {
                $this->rules[$class['name']] = $class['rule'];
            }

            //Build an array that links the classnames to their corresponding style data
            $this->classStyles[$class['name']] = $class['data'];

            //Build an array containing classes for any plugins that have been used
            if ($class['name'] == 'prose') {
                $this->proseClasses = Style::where('category', 'prose')->get();
            }
        }

        //Build the css by iterating over the categorizedClasses array 
        foreach($this->categorizedClasses as $breakpointName => $breakpoint) {
            if ($breakpointName == 'sm:' && !empty($breakpoint)) {
                $this->css .= ' @media (min-width: 640px) {';
            }
            if ($breakpointName == 'md:' && !empty($breakpoint)) {
                $this->css .= ' @media (min-width: 768px) {';
            }
            if ($breakpointName == 'lg:' && !empty($breakpoint)) {
                $this->css .= ' @media (min-width: 1024px) {';
            }
            if ($breakpointName == 'xl:' && !empty($breakpoint)) {
                $this->css .= ' @media (min-width: 1280px) {';
            }
            if ($breakpointName == '2xl:' && !empty($breakpoint)) {
                $this->css .= ' @media (min-width: 1536px) {';
            }
            foreach($breakpoint as $className => $classValue) {
                $classValue = null;
                if (
                    $breakpointName == 'sm:' || 
                    $breakpointName == 'md:' ||
                    $breakpointName == 'lg:' ||
                    $breakpointName == 'xl:' ||
                    $breakpointName == '2xl:'
                ) {
                    if (!empty($this->classes[substr($className, 3)])) {
                        $classValue = $this->classes[substr($className, 3)];
                        $className = str_replace(":", "\\:", $className);
                    }
                } else {
                    if (!empty($this->classes[$className])) {
                        $classValue = $this->classes[$className];
                    }
                }
                
                if (!empty($classValue)) {
                    $styles = json_decode($classValue, true);
                    $count = count($styles);
                    if ($count > 1) {
                        $classValue = str_replace('}', ';}', str_replace(',', ';', $classValue));
                    }
                    $this->css .= '.'.$className . (!empty($this->rules[$className]) ? $this->rules[$className] : '') . str_replace('}', ';}', str_replace('"', '', $classValue));
                }
            }
            if ($breakpointName != 'nobreakpoint') {
                $this->css .= '}';
            }
        }
        if (!empty($this->proseClasses)) {
            foreach($this->proseClasses as $proseClass) {
                $this->css .= '.'.$proseClass['name'] . (!empty($proseClass['rule']) ? ' ' . $proseClass['rule'] : '') . str_replace('}', ';}', str_replace(',', ';', str_replace('"', '', $proseClass['data'])));
            }
        }
        return $this->css;

        //or place the css in a file file_put_contents(base_path() . '/tailwind.css', $query);
    }
}