<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Edujugon\Laradoo\Odoo;
use Illuminate\Support\Facades\Log; // تأكد من استيراد Log

class OdooTestController extends Controller
{

//--------------------------------------------{ C O N N E C T I O N }-------------------------------------------------\\
    public function testConnection()
    {
        try {
            // إنشاء اتصال Odoo
            $odoo = new Odoo();
            $odoo->host(env('ODOO_URL'))
                 ->db(env('ODOO_DB'))
                 ->username(env('ODOO_USERNAME'))
                 ->password(env('ODOO_PASSWORD'))
                 ->connect();

            // إعادة توجيه المستخدم إلى واجهة Odoo
            return redirect(env('ODOO_URL'));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to connect to Odoo', 'error' => $e->getMessage()]);
        }
    }


 //--------------------------------------------{ C R E A T E   C U S T O M E R }-------------------------------------------------\\
    public function createCustomer(Request $request)
    {
        try {
            // التحقق من صحة البيانات المدخلة
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'street' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'state_id' => 'required|integer',
                'country_id' => 'required|integer',
                'zip' => 'required|string|max:20',
            ]);

            // إنشاء اتصال Odoo
            $odoo = new Odoo();
            $odoo->host(env('ODOO_URL'))
                 ->db(env('ODOO_DB'))
                 ->username(env('ODOO_USERNAME'))
                 ->password(env('ODOO_PASSWORD'))
                 ->connect();
    
            $customerData = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'street' => $validatedData['street'],
                'city' => $validatedData['city'],
                'state_id' => $validatedData['state_id'],
                'country_id' => $validatedData['country_id'],
                'zip' => $validatedData['zip'],
            ];
    
            // إنشاء العميل في Odoo
            $customerId = $odoo->create('res.partner', $customerData);
    
            return response()->json(['message' => 'Customer created successfully', 'customer_id' => $customerId]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create customer', 'error' => $e->getMessage()]);
        }
    }

    public function createInvoice(Request $request)
    {
        try {
            // Validate input data
            $validatedData = $request->validate([
                'customer_id' => 'required|integer',
                'invoice_date' => 'required|date',
                'invoice_lines' => 'required|array',
                'invoice_lines.*.product_id' => 'required|integer',
                'invoice_lines.*.quantity' => 'required|numeric',
                'invoice_lines.*.price_unit' => 'required|numeric',
            ]);
    
            // Create Odoo connection
            $odoo = new Odoo();
            $odoo->host(env('ODOO_URL'))
                 ->db(env('ODOO_DB'))
                 ->username(env('ODOO_USERNAME'))
                 ->password(env('ODOO_PASSWORD'))
                 ->connect();
        
            // Prepare invoice data
            $invoiceData = [
                'partner_id' => $validatedData['customer_id'],
                'invoice_date' => $validatedData['invoice_date'],
                'move_type' => 'out_invoice', // Invoice type
                'invoice_line_ids' => [],
            ];
    
            // Prepare invoice lines data
            foreach ($validatedData['invoice_lines'] as $line) {
                $invoiceData['invoice_line_ids'][] = [
                    0 => 0, // Create new record
                    0 => [
                        'product_id' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'price_unit' => $line['price_unit'],
                        'account_id' => 1, // Ensure this is a valid account ID
                        // Add other required fields if necessary
                    ],
                ];
            }
        
            // Debugging: Log invoice data
            \Log::info('Creating invoice with data:', $invoiceData);
    
            // Create the invoice in Odoo
            $invoiceId = $odoo->create('account.move', $invoiceData);
        
            return response()->json(['message' => 'Invoice created successfully', 'invoice_id' => $invoiceId]);
        } catch (\Exception $e) {
            // Debugging: Log error details
            \Log::error('Failed to create invoice', ['error' => $e->getMessage()]);
    
            return response()->json(['message' => 'Failed to create invoice', 'error' => $e->getMessage()]);
        }
    }
}    