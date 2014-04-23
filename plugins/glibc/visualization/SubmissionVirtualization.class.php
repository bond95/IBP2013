<?php

/**
 * SubmissionVisualization short summary.
 *
 * SubmissionVisualization description.
 *
 * @version 1.0
 * @author Filip
 */
class SubmissionVisualization
{
    // Constants
    const PAGE_SIZE = 100;
    
    /**
     * Get data depth based on component type
     * @param mixed $type 
     * @return mixed
     */
    public static function GetDataDepth($type)   {
        // Decide data depth
        switch($type)   {
            // Get results for google chart
            case SubmissionOverviewType::GOOGLE_CHART:
                return DataDepth::RESULT;
            
            case SubmissionOverviewType::VIEWLIST:
                return DataDepth::RESULT;
            
            default:
                return DataDepth::SUBMISSION;
        }
    }
    
    /**
     * Get view components for project view
     * @return mixed
     */
    public static function GetViewComponents()  {
        return array(
                SubmissionOverviewComponent::GOOGLE_CHART,
                SubmissionOverviewComponent::VIEWLIST
            );
    }
    
    /**
     * Create submission object for visualization of given type
     * @param SubmissionTSE $submission 
     * @return mixed
     */
    public static function Visualize(SubmissionTSE $submission, $type, $meta) {
        // Get view based on demanded ty
        switch($type)   {
            // Get google chart
            case SubmissionOverviewType::GOOGLE_CHART:
                return SubmissionVisualization::GetSubmissionOverviewChart($submission)->ExportObject();
            
            // Get view list
            case SubmissionOverviewType::VIEWLIST:
                return SubmissionVisualization::GetSubmissionOverviewList($submission, $meta)->ExportObject();
            
            // Return null if no assigned component was found
            default:
                return null;
        }
    }
    
    /**
     * Get submission overview list data for visualization
     * @param SubmissionTSE $project 
     * @return mixed
     */
    private static function GetSubmissionOverviewList(SubmissionTSE $submission, $page)    {
        // Initialize list
        $submissionOverviewList = new SubmissionOverviewList();
        
        // Initialize items count
        $itemsCount = 0;
        
        foreach ($submission->GetCategories() as $category)
        {
            $itemsCount += $category->GetNumberOfTestCases();
            $category->SpliceTestCases(($page - 1) * SubmissionVisualization::PAGE_SIZE, SubmissionVisualization::PAGE_SIZE);
            
            // Create new list item
            $submissionOverviewListItem = new SubmissionOverviewListItem($category);
            // Add item to list
            $submissionOverviewList->AddItem($submissionOverviewListItem);
        }
        
        // Set page number
        $submissionOverviewList->SetPage($page);
        
        // Set number of records
        $submissionOverviewList->SetItemsCount($itemsCount);
        
        // Set available views
        $submissionOverviewList->AddView(SubmissionOverviewListType::GroupedView);
        $submissionOverviewList->AddView(SubmissionOverviewListType::ListView);
        
        // Return result
        return $submissionOverviewList;
    }
    
    /**
     * Get Submission Overview Chart for visualization
     * @param SubmissionTSE $submission 
     * @return mixed
     */
    private static function GetSubmissionOverviewChart(SubmissionTSE $submission)    {
        // Initialize Google chart object
        $submissionOverviewChart = new SubmissionOverviewChart();
        $googleChart = new GoogleChart();
        $googleChart->setType(GCType::PIE_CHART);
        
        // Assign options to chart
        $googleChart->setOptions(SubmissionVisualization::GetGCOptions());
        
        // Get data
        $googleChart->setData(SubmissionVisualization::GetGCData($submission));
        
        // Set chart as chart overview
        $submissionOverviewChart->SetChart($googleChart);
        
        // return google chart object
        return $submissionOverviewChart;
    }
    
    /**
     * Get data for submission google chart
     * @param SubmissionTSE $submission 
     * @return mixed
     */
    private static function GetGCData(SubmissionTSE $submission) {
        // Initialize data
        $gcData = new GCData();
        
        // Initialize results list
        $subResults = array();
        
        // First, we need to get all results of given submission
        foreach ($submission->GetCategories() as $category) {
            // Iterate through each test case
            foreach ($category->GetTestCases() as $testCase)    {
                // Iterate through each result and get "status" value
                foreach ($testCase->GetResults() as $result) {
                    $subResults[] = $result->GetValue();
                }
            }
        }
        
        // Create STATUS column
        $gcStatusCol = new GCCol();
        $gcStatusCol->setId("error");
        $gcStatusCol->setLabel("Error");
        $gcStatusCol->setType("string");
        // Add column to data set
        $gcData->AddColumn($gcStatusCol);
        
        // Create VALUE column
        $gcValueCol = new GCCol();
        $gcValueCol->setId("value");
        $gcValueCol->setLabel("Value");
        $gcValueCol->setType("number");
        // Add column to data set
        $gcData->AddColumn($gcValueCol);
        
        // Then, get unique array of column values
        $columns = array_unique($subResults);
        
        // Create rows with cells
        $lSubResults = new LINQ($subResults);
        foreach ($columns as $column) {
            // Create new row
            $gcRow = new GCRow();
            
            // Create label cell
            $gcLabelCell = new GCCell();
            $gcLabelCell->setValue($column);
            // Add cell to row
            $gcRow->AddCell($gcLabelCell);
            
            // Create value cell
            $gcValueCell = new GCCell();
            $gcValueCell->setValue($lSubResults->Where(null, LINQ::IS_EQUAL, $column)->Count());
            // Add cell to row
            $gcRow->AddCell($gcValueCell);
            
            // Add row to data
            $gcData->AddRow($gcRow);
        }
        
        // Return result
        return $gcData;
    }
    
    /**
     * Get google chart options
     * @return mixed
     */
    private static function GetGCOptions()  {
        // Create options and set all in it
        $gcOptions = new GCOptions();
        $gcOptions->setTitle("Errors overview");
        
        // Return options
        return $gcOptions;
    }
}
