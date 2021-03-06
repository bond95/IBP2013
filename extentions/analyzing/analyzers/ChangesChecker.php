<?php
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Library.utility.php');

Library::using(Library::UTILITIES);

class ChangesChecker
{
    const ANALYZER_ID = "ChangesChecker";
    const JS_CONTROLLER = "changes_checker.js";
    private $is_interesting = false;
    public function analyze(SubmissionTSE $submission, LINQ $submissionList, $plugin)
    {
        $this->is_interesting = false;
        if ($plugin == "systemtap") {
            $good = 0;
            $bad = 0;
            $strange = 0;

            if ($submissionList->IsEmpty()) {
                return new ValidationResult(array());
            }
            $submission1 = $submissionList->Last();
            $submission2 = $submission;
            
            foreach ($submission1->GetCategories() as $category) {
                $category2 = $submission2->GetCategoryByName($category->GetName());
                if (is_null($category2)) {
                    continue;
                }
                foreach ($category->GetTestCases() as $testCase) {
                    $testCase2 = $category2->GetTestCaseByName($testCase->GetName());
                    if (is_null($testCase2)) {
                        continue;
                    }
                    foreach ($testCase->GetResults() as $result) {
                        $result2 = $testCase2->GetResultByKey($result->GetKey());
                        if (is_null($result2)) {
                            continue;
                        }
                        if ($result->GetValue() != $result2->GetValue()) {
                            if ($result->GetValue() == "PASS" &&
                                $result2->GetValue() == "FAIL") {
                                $bad++;
                            } elseif ($result->GetValue() == "FAIL" &&
                                $result2->GetValue() == "PASS" ) {
                                $good++;
                            } elseif ($result->GetValue() == "FAIL" &&
                                $result2->GetValue() == "ERROR") {
                                $strange++;
                            }
                        }
                    }
                }
            }
            $res = new stdClass();
            $res->Good = $good;
            $res->Bad = $bad;
            $res->Strange = $strange;
            if ($good || $bad || $strange) {
                $this->is_interesting = true;
            }
            $validation = new ValidationResult(array(json_encode($res)));
            return $validation;
        }
    }

    public function isInteresting()
    {
        return $this->is_interesting;
    }

    public function Visualize(LINQ $data)
    {
        $visualize = array();
        foreach ($data->ToList() as $value) {
            $visualize[$value->GetSubmission()] = json_decode($value->GetResult());
        }
        return $visualize;
    }

    public function VisualizeSingle($data)
    {
        $visualize = null;
        if ($data) {
            $visualize = json_decode($data->GetResult());
        }
        return $visualize;
    } 
}
