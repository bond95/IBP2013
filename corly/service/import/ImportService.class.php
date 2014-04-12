<?php
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Library.utility.php');

// Get libraries
Library::using(Library::UTILITIES);
Library::using(Library::CORLY_DAO_IMPLEMENTATION_PLUGIN);
Library::using(Library::CORLY_SERVICE_SUITE);

/**
 * ImportService short summary.
 *
 * ImportService description.
 *
 * @version 1.0
 * @author Filip
 */
class ImportService
{
    // Services
    private $SubmissionService;
    // Daos
    private $PluginDao; 
    
    /**
     * Import service constructor
     */
    public function __construct()   {
        $this->SubmissionService = new SubmissionService();
        $this->PluginDao = new PluginDao();
    }
    
    /**
     * Import file with given plugin
     */
    public function Import($data, $file)    {
        // Init validation
        $validation = new ValidationResult($data);
        
        // Validate data properties
        $validation->CheckNotNullOrEmpty('Plugin', "Plugin has to be set");
        $validation->CheckNotNullOrEmpty('Project', "Project has to be set");
        
        // Check validation result
        if (!$validation->IsValid)  {
            return $validation;
        }
        
        // Initialize file parser
        $fileParser = new FileParser($file);
        
        // Get the right plugin to import
        $this->GetImportPlugin($validation);
        
        // Check if importer was included
        if (!class_exists('Importer'))  {
            $validation->AddError("Importer for given plugin was not found");
            return $validation;
        }
        
        // Import file by given plugin
        $importValidation = Importer::Import($validation, $fileParser);
        
        // Check import validation
        if (!$importValidation->IsValid)    {
            $validation->Append($importValidation);
            return $validation;
        }
        
        // Save imported data into database
        $this->SubmissionService->Save($importValidation->Data, $validation->Data->Project);
        
        // Return validation
        return $validation;
    }
    
    /**
     * Get given plugin to import data
     */
    private function GetImportPlugin(ValidationResult $validation)  {
        // Prepare plugin object to load
        $plugin = new stdClass();
        $plugin->Id = $validation->Data->Plugin;
        
        // Load plugin 
        $plugin = $this->PluginDao->Load($plugin);
        
        // Load plugin structure
        Library::using(Library::PLUGINS .DIRECTORY_SEPARATOR. $plugin->Root);
    }
}
