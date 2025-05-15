<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport; // Make sure this namespace is correct
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;         // Import Log facade
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException; // Import specific exception for validation failures

class ExcelImportController extends Controller
{
    /**
     * Show the form to upload the Excel file.
     *
     * @return \Illuminate\View\View
     */
    public function importForm()
    {
        // Ensure this view exists: resources/views/import-form.blade.php
        // The view should have a form with method="POST", enctype="multipart/form-data",
        // and an input with type="file" and name="file".
        // Also ensure it can display session 'success' and 'error' messages, and validation errors ($errors).
        return view('import-form');
    }

    /**
     * Handle the import process from the uploaded Excel file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        // 1. Validate the uploaded file itself
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv' // Allow common spreadsheet types
        ]);

        $file = $request->file('file');

        // Using default log channel, change 'imports' if you set up a specific channel
        Log::info('Starting Excel file import process.', [
            'originalName' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mimeType' => $file->getMimeType(),
        ]);

        // 2. Try to perform the import, catching potential exceptions
        try {
            // Pass the file directly to the import class
            Excel::import(new UsersImport, $file);

            Log::info('Excel file imported successfully.', [
                'originalName' => $file->getClientOriginalName(),
            ]);

            // --- Redirect BACK with success message ---
            return back()->with('success', 'Users imported successfully!');
            // --- End of change ---

        } catch (ValidationException $e) {
            // Catch validation errors specifically from the UsersImport rules() method
            $failures = $e->failures(); // Get validation failures object
            $errorMessages = [];

            // Format errors for logging and potentially display
            foreach ($failures as $failure) {
                 $errorMessages[] = "Row " . $failure->row() . ": " . implode(', ', $failure->errors())
                                  . " for attribute '" . $failure->attribute()
                                  . "' (Value: '" . ($failure->values()[$failure->attribute()] ?? 'N/A') . "')";
            }

            Log::error('Excel Import Validation Failed.', [
                'originalName' => $file->getClientOriginalName(),
                'errors' => $errorMessages // Log formatted errors
            ]);

            // Redirect BACK with specific validation errors for the user
            return back()
                   ->with('error', 'Import failed due to validation errors in the file. Please check the details and correct the file.')
                   ->with('validation_failures', $errorMessages); // Pass formatted errors (view needs to handle this)

        } catch (\Exception $e) {
            // Catch any other general exceptions during the import process
            Log::error('Excel Import General Error occurred.', [
                 'originalName' => $file->getClientOriginalName(),
                 'message' => $e->getMessage(),
                 'trace' => $e->getTraceAsString() // Log full stack trace for detailed debugging
            ]);

            // Redirect BACK with a generic but informative error message
            return back()
                   ->with('error', 'An unexpected error occurred during the import. Please check the system logs or contact support. Error: ' . $e->getMessage());
        }
    }
}