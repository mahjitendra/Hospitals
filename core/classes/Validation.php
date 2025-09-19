<?php

/**
 * Form Validation Class
 * 
 * Handles form validation with various rules
 */
class Validation
{
    private $data = [];
    private $rules = [];
    private $messages = [];
    private $errors = [];
    private $customMessages = [];
    
    /**
     * Create validator instance
     */
    public function make($data, $rules, $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $messages;
        $this->errors = [];
        
        $this->validate();
        
        return $this;
    }
    
    /**
     * Perform validation
     */
    private function validate()
    {
        foreach ($this->rules as $field => $rules) {
            $rulesArray = is_string($rules) ? explode('|', $rules) : $rules;
            
            foreach ($rulesArray as $rule) {
                $this->validateField($field, $rule);
            }
        }
    }
    
    /**
     * Validate individual field
     */
    private function validateField($field, $rule)
    {
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $ruleValue = $ruleParts[1] ?? null;
        
        $value = $this->data[$field] ?? null;
        
        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, 'required');
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'email');
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < $ruleValue) {
                    $this->addError($field, 'min', ['min' => $ruleValue]);
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > $ruleValue) {
                    $this->addError($field, 'max', ['max' => $ruleValue]);
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, 'numeric');
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, 'integer');
                }
                break;
                
            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    $this->addError($field, 'alpha');
                }
                break;
                
            case 'alpha_num':
                if (!empty($value) && !ctype_alnum($value)) {
                    $this->addError($field, 'alpha_num');
                }
                break;
                
            case 'date':
                if (!empty($value) && !$this->isValidDate($value)) {
                    $this->addError($field, 'date');
                }
                break;
                
            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, 'url');
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, 'confirmed');
                }
                break;
                
            case 'unique':
                if (!empty($value) && $this->isUnique($field, $value, $ruleValue)) {
                    $this->addError($field, 'unique');
                }
                break;
                
            case 'exists':
                if (!empty($value) && !$this->exists($field, $value, $ruleValue)) {
                    $this->addError($field, 'exists');
                }
                break;
                
            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if (!empty($value) && !in_array($value, $allowedValues)) {
                    $this->addError($field, 'in', ['values' => implode(', ', $allowedValues)]);
                }
                break;
                
            case 'not_in':
                $disallowedValues = explode(',', $ruleValue);
                if (!empty($value) && in_array($value, $disallowedValues)) {
                    $this->addError($field, 'not_in');
                }
                break;
                
            case 'regex':
                if (!empty($value) && !preg_match($ruleValue, $value)) {
                    $this->addError($field, 'regex');
                }
                break;
                
            case 'phone':
                if (!empty($value) && !$this->isValidPhone($value)) {
                    $this->addError($field, 'phone');
                }
                break;
                
            case 'strong_password':
                if (!empty($value) && !$this->isStrongPassword($value)) {
                    $this->addError($field, 'strong_password');
                }
                break;
        }
    }
    
    /**
     * Add validation error
     */
    private function addError($field, $rule, $params = [])
    {
        $message = $this->getMessage($field, $rule, $params);
        $this->errors[$field][] = $message;
    }
    
    /**
     * Get error message
     */
    private function getMessage($field, $rule, $params = [])
    {
        $key = "{$field}.{$rule}";
        
        if (isset($this->customMessages[$key])) {
            return $this->customMessages[$key];
        }
        
        if (isset($this->customMessages[$rule])) {
            return $this->customMessages[$rule];
        }
        
        $messages = [
            'required' => 'The :field field is required.',
            'email' => 'The :field must be a valid email address.',
            'min' => 'The :field must be at least :min characters.',
            'max' => 'The :field may not be greater than :max characters.',
            'numeric' => 'The :field must be a number.',
            'integer' => 'The :field must be an integer.',
            'alpha' => 'The :field may only contain letters.',
            'alpha_num' => 'The :field may only contain letters and numbers.',
            'date' => 'The :field is not a valid date.',
            'url' => 'The :field format is invalid.',
            'confirmed' => 'The :field confirmation does not match.',
            'unique' => 'The :field has already been taken.',
            'exists' => 'The selected :field is invalid.',
            'in' => 'The selected :field must be one of: :values.',
            'not_in' => 'The selected :field is invalid.',
            'regex' => 'The :field format is invalid.',
            'phone' => 'The :field must be a valid phone number.',
            'strong_password' => 'The :field must contain at least 8 characters with uppercase, lowercase, number and special character.',
        ];
        
        $message = $messages[$rule] ?? 'The :field is invalid.';
        
        // Replace placeholders
        $message = str_replace(':field', ucfirst(str_replace('_', ' ', $field)), $message);
        
        foreach ($params as $key => $value) {
            $message = str_replace(":{$key}", $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Check if validation failed
     */
    public function fails()
    {
        return !empty($this->errors);
    }
    
    /**
     * Check if validation passed
     */
    public function passes()
    {
        return empty($this->errors);
    }
    
    /**
     * Get all errors
     */
    public function errors()
    {
        return $this->errors;
    }
    
    /**
     * Get first error for field
     */
    public function first($field)
    {
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Get validated data
     */
    public function validated()
    {
        if ($this->fails()) {
            throw new Exception('Validation failed');
        }
        
        $validated = [];
        foreach ($this->rules as $field => $rules) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }
        
        return $validated;
    }
    
    /**
     * Check if date is valid
     */
    private function isValidDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Check if value is unique in database
     */
    private function isUnique($field, $value, $table)
    {
        $db = Database::getInstance();
        return !$db->exists($table, "{$field} = :value", ['value' => $value]);
    }
    
    /**
     * Check if value exists in database
     */
    private function exists($field, $value, $table)
    {
        $db = Database::getInstance();
        return $db->exists($table, "{$field} = :value", ['value' => $value]);
    }
    
    /**
     * Validate phone number
     */
    private function isValidPhone($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid length (10-15 digits)
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }
    
    /**
     * Check if password is strong
     */
    private function isStrongPassword($password)
    {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password) &&
               preg_match('/[^A-Za-z0-9]/', $password);
    }
    
    /**
     * Add custom validation rule
     */
    public static function extend($rule, $callback)
    {
        // This would be implemented to add custom validation rules
    }
}