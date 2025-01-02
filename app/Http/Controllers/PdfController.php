<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Factor;
use setasign\Fpdi\Fpdi;
use App\Models\Question;
use App\Models\PdfTemplate;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\QuesitonCategory;


class PdfController extends Controller
{
    public function generatePDF()
    {
        $data = ['name' => 'John Doe', 'type' => 'Example Type', 'image' => 'path/to/image.jpg', 'website' => 'https://example.com', 'score' => 85];
        $pdf = Pdf::loadView('backend.template.v1.pdf_template', $data);
        return $pdf->download('document.pdf');
    }

    public function editPDF()
    {
        // Path to the existing PDF
        $pathToPdf = storage_path('app/public/v1.pdf'); // Create new FPDI instance
        $pdf = new FPDI(); // Set the source file
        $pageCount = $pdf->setSourceFile($pathToPdf); // Define the text and positions for each page
        $data = [
            1 => [
                'name' => 'Ebony Williams,',
                'x' => 11,
                'y' => 139.8,
                'type' => 'Attorney at Law, PLLC',
                'type_x' => 10.5,
                'type_y' => 150,
                'date' => 'December 30, 2024',
                'date_x' => 40,
                'date_y' => 249,
                'website' => 'www.ebonyatlaw.com',
                'website_x' => 49,
                'website_y' => 260
            ],

            2 => [
                'name_2' => 'Ebony Williams,',
                'x' => 62.5,
                'y' => 39.5,

                'type_2' => 'Attorney at Law, PLLC',
                'type2_x' => 45,
                'type2_y' => 54,
                'score' => ' 41/100',
                'score_x' => 116,
                'score_y' => 222,
                'image' => storage_path('app/public/images/reasons.jpg'),
                'img_x' => 37.8,
                'img_y' => 129,
                'img_w' => 141,
                'img_h' => 80
            ],
            // 3 => ['text' => 'Text on Page 3', 'x' => 70, 'y' => 70],
            // Add more pages as needed
        ];
        // Loop through all pages
        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($templateId); // Check if there is any text to add on this page
            if (isset($data[$i])) {


                if (isset($data[$i]['name'])) {
                    $pdf->SetFont('helvetica', 'B', 22);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetXY($data[$i]['x'], $data[$i]['y']);
                    $pdf->Write(0, $data[$i]['name']);
                }
                if (isset($data[$i]['type'])) {
                    $pdf->SetFont('helvetica', 'B', 22.5);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetXY($data[$i]['type_x'], $data[$i]['type_y']);
                    $pdf->Write(0, $data[$i]['type']);
                }

                if (isset($data[$i]['date'])) {
                    $pdf->SetFont('helvetica', '', 16);
                    $pdf->SetXY($data[$i]['date_x'], $data[$i]['date_y']);
                    $pdf->Write(0, $data[$i]['date']);
                }

                if (isset($data[$i]['website'])) {
                    $pdf->SetFont('helvetica', '', 16);
                    $pdf->SetXY($data[$i]['website_x'], $data[$i]['website_y']);
                    $pdf->Write(0, $data[$i]['website']);
                }

                if (isset($data[$i]['name_2'])) {
                    $pdf->SetFont('helvetica', 'B', 33);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetXY($data[$i]['x'], $data[$i]['y']);
                    $pdf->Write(0, $data[$i]['name_2']);
                }

                if (isset($data[$i]['type_2'])) {
                    $pdf->SetFont('helvetica', 'B', 33);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetXY($data[$i]['type2_x'], $data[$i]['type2_y']);
                    $pdf->Write(0, $data[$i]['type_2']);
                }

                if (isset($data[$i]['score'])) {
                    $pdf->SetFont('helvetica', 'B', 18);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetXY($data[$i]['score_x'], $data[$i]['score_y']);
                    $pdf->Write(0, $data[$i]['score']);
                }

                if (isset($data[$i]['image'])) {
                    $pdf->Image(
                        $data[$i]['image'],
                        $data[$i]['img_x'],
                        $data[$i]['img_y'],
                        $data[$i]['img_w'],
                        $data[$i]['img_h']
                    );
                }
            }
        } // Output the edited PDF
        return response($pdf->Output('S', 'edited.pdf'))->header('Content-Type', 'application/pdf');
    }

    public function getPdfList()
    {
        $pdfs = PdfTemplate::all();
        return view('backend.pdf.list', compact('pdfs'));
    }


    public function uploadPDF(Request $request)
    {
        $request->validate(['pdf' => 'required|mimes:pdf|max:2048',]);
        if ($request->file('pdf')) {
            $pdfFolderPath = public_path('pdf');
            if (!file_exists($pdfFolderPath)) {
                mkdir($pdfFolderPath, 0755, true);
            }
        }
        if ($request->file('pdf')) {
            $fileName = time() . '.' . $request->pdf->extension();
            $request->pdf->move($pdfFolderPath, $fileName);
            // Determine the new version
            $latestVersion = PdfTemplate::orderBy('created_at', 'desc')->first();
            $newVersion = $latestVersion ? 'v' . (substr($latestVersion->version, 1) + 1) : 'v1'; // Save to database
            $pdfTemplate = new PdfTemplate();
            $pdfTemplate->name = $fileName;
            $pdfTemplate->version = $newVersion;
            $pdfTemplate->save();
            return response()->json(['success' => 'PDF uploaded successfully. Version: ' . $newVersion]);
        }
        return response()->json(['error' => 'File upload failed.'], 500);
    }

    public function getFactorList(Request $request)
    {
        $query = Question::with('category');

        if ($request->filled('type')) {
            $query->where('question_category_id', $request->input('type'));
        }

        $factors = $query->paginate(25);

        $pdfs = PdfTemplate::all();
        $categories = QuesitonCategory::all();

        if ($request->ajax()) {
            return response()->json(['factors' => $factors, 'pdfs' => $pdfs, 'categories' => $categories]);
        }
        return view('backend.factors.list', compact('factors', 'pdfs', 'categories'));
    }

    public function saveFactor(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:255',
            'category_id' => 'required|exists:question_categories,id', // Validate existence in the database
        ]);

        // Create a new Question instance
        $factor = new Question();
        $factor->question_category_id = $request->input('category_id');
        $factor->text = $request->input('text');
        $factor->save();

        // Load the category relationship for the response
        $factor->load('category');

        return response()->json([
            'success' => 'Factor saved successfully.',
            'data' => [
                'id' => $factor->id,
                'text' => $factor->text,
                'type' => $factor->category->name,
                'created_at' => $factor->created_at,
            ],
        ]);
    }

    public function getCategoryList()
    {
        $categories = QuesitonCategory::all();
        return view('backend.category.list', compact('categories'));
    }

    public function saveCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = QuesitonCategory::create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'newItem' => $category,
        ]);
    }

    public function makeReport()
    {
        $clients = Client::all();
        $questions = Question::all();
      return view('backend.report.add_report', compact('clients', 'questions'));
    }
}
