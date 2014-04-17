<?php

/**
 * ProjectOverviewList short summary.
 *
 * ProjectOverviewList description.
 *
 * @version 1.0
 * @author Filip
 */
class ProjectOverviewList
{
    /**
     * List of submissions
     * @var mixed
     */
    private $Submissions;
    
    /**
     * Project overview list constructor
     */
    public function __construct()   {
        $this->Submissions = array();
    }
    
    /**
     * Add submission to project overview list
     * @param mixed $submission 
     */
    public function AddSubmission($submission)  {
        $this->Submissions[] = $submission;
    }
    
    /**
     * Export object for serialization
     * @return mixed
     */
    public function ExportObject()  {
        // Init object
        $projectOverviewList = new stdClass();
        
        // Set values
        $projectOverviewList->Submissions = $this->Submissions;
        
        // return object
        return $projectOverviewList;
    }
    
    
}
