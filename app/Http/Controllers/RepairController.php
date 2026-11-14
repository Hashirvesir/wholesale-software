<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\Product;
use App\Models\RepairBrand;
use App\Models\RepairDevice;
use App\Models\RepairDevicePart;
use App\Models\RepairModel;
use App\Models\RepairOrder;
use App\Models\RepairService;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepairController extends Controller
{
    /**
     * List all repair orders with status filtering & live search.
     */
    public function index(Request $request)
    {
        $query = RepairOrder::with(['customer', 'devices.technician'])->latest();

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search Filter (Order No, Customer Name/Phone, or Device Tracking / IMEI)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('devices', function ($dq) use ($search) {
                        $dq->where('device_tracking_no', 'like', "%{$search}%")
                            ->orWhere('imei', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        }

        // Date Filter
        if ($request->filled('date_from')) {
            $query->whereDate('received_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('received_date', '<=', $request->date_to);
        }

        $repairs = $query->paginate(15)->withQueryString();

        // Dashboard counters
        $counts = [
            'all' => RepairOrder::count(),
            'pending' => RepairOrder::where('status', 'pending')->count(),
            'in_progress' => RepairOrder::where('status', 'in_progress')->count(),
            'completed' => RepairOrder::where('status', 'completed')->count(),
            'delivered' => RepairOrder::where('status', 'delivered')->count(),
        ];

        return view('repairs.index', compact('repairs', 'counts'));
    }

    /**
     * Show repair intake form for single or multiple devices.
     */
    public function create()
    {
        $orderNo = RepairOrder::generateOrderNo();
        // Load active customers with current balance (matching purchase/sale dropdowns)
        $customers = Party::where('type', 'customer')->where('status', 1)->orderBy('name')->get()->map(function($c) {
            $c->current_balance = $c->getCurrentBalance();
            return $c;
        });
        
        // Load active products with stock sum and warehouse stocks
        $products = Product::where('status', 1)
            ->with(['stocks'])
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                $p->total_stock = (float) $p->stocks->sum('qty');
                $p->warehouse_stocks = $p->stocks->pluck('qty', 'warehouse_id');
                return $p;
            });
            
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);
        $technicians = User::orderBy('name')->get(['id', 'name']);
        $defaultWarehouse = $warehouses->first();

        // Load catalog brands with models & catalog services with default rates
        $brands = RepairBrand::where('status', 1)->with(['models' => function($q) {
            $q->where('status', 1)->orderBy('series')->orderBy('sort_order')->orderBy('name');
        }])->orderBy('sort_order')->orderBy('name')->get();

        $services = RepairService::where('status', 1)->orderBy('sort_order')->orderBy('category')->get();

        return view('repairs.create', compact('orderNo', 'customers', 'products', 'warehouses', 'technicians', 'defaultWarehouse', 'brands', 'services'));
    }

    /**
     * Store new repair order with multiple devices & optional spare parts.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|min:2|max:255',
            'customer_phone' => 'required|string|min:7|max:50',
            'received_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'advance_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'devices' => 'required|array|min:1',
            'devices.*.model' => 'required|string|max:255',
            'devices.*.problem_description' => 'required|string',
            'devices.*.labor_cost' => 'nullable|numeric|min:0',
            'devices.*.warranty_days' => 'nullable|integer|min:0|max:365',
            'devices.*.parts.*.product_id' => 'nullable|exists:products,id',
            'devices.*.parts.*.quantity' => 'nullable|integer|min:1',
            'devices.*.parts.*.unit_price' => 'nullable|numeric|min:0',
        ], [
            'customer_name.required' => 'Customer ka naam likhna lazmi hai.',
            'customer_name.min' => 'Customer ka naam kam az kam 2 huroof ka hona chahiye.',
            'customer_phone.required' => 'Customer ka mobile / WhatsApp number likhna lazmi hai.',
            'customer_phone.min' => 'Customer ka phone number kam az kam 7 digits ka hona chahiye.',
            'devices.required' => 'Kam az kam 1 mobile add karna lazmi hai.',
            'devices.min' => 'Kam az kam 1 mobile add karna lazmi hai.',
            'devices.*.model.required' => 'Har mobile ka Model likhna lazmi hai (e.g. Samsung A32, iPhone 11).',
            'devices.*.problem_description.required' => 'Mobile ka masla / fault likhna ya service select karna lazmi hai.',
            'devices.*.labor_cost.numeric' => 'Labor charges sirf number hone chahiye.',
            'devices.*.parts.*.quantity.min' => 'Spare part ki quantity kam az kam 1 honi chahiye.',
            'devices.*.parts.*.unit_price.min' => 'Spare part ki price 0 ya is se zyada honi chahiye.',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Find or link party
            $partyId = $request->party_id;
            if (!$partyId && $request->filled('customer_phone')) {
                $existingParty = Party::where('phone', $request->customer_phone)->first();
                if ($existingParty) {
                    $partyId = $existingParty->id;
                }
            }

            $orderNo = RepairOrder::generateOrderNo();
            $advance = floatval($request->advance_amount ?? 0);
            $receivedDate = $request->filled('received_date') ? Carbon::parse($request->received_date) : now();

            $order = RepairOrder::create([
                'order_no' => $orderNo,
                'party_id' => $partyId,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'received_date' => $receivedDate,
                'expected_delivery_date' => $request->expected_delivery_date ? Carbon::parse($request->expected_delivery_date) : null,
                'total_devices' => count($request->devices),
                'total_parts_cost' => 0,
                'total_labor_cost' => 0,
                'total_amount' => 0,
                'advance_amount' => $advance,
                'balance_amount' => 0,
                'payment_method' => $request->payment_method ?? 'cash',
                'status' => 'pending',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            $totalPartsCost = 0;
            $totalLaborCost = 0;
            $deviceIndex = 1;

            foreach ($request->devices as $devData) {
                $trackingNo = $order->order_no . '-' . $deviceIndex++;
                $labor = floatval($devData['labor_cost'] ?? 0);
                $devicePartsCost = 0;

                $checklist = $devData['condition_checklist'] ?? [];

                $device = RepairDevice::create([
                    'repair_order_id' => $order->id,
                    'device_tracking_no' => $trackingNo,
                    'brand' => $devData['brand'] ?? null,
                    'model' => $devData['model'],
                    'imei' => $devData['imei'] ?? null,
                    'color' => $devData['color'] ?? null,
                    'security_type' => $devData['security_type'] ?? 'none',
                    'security_code' => $devData['security_code'] ?? null,
                    'problem_description' => $devData['problem_description'],
                    'services' => !empty($devData['services']) ? (is_array($devData['services']) ? $devData['services'] : json_decode($devData['services'], true)) : null,
                    'accessories_received' => $devData['accessories_received'] ?? null,
                    'condition_checklist' => is_array($checklist) ? $checklist : json_decode($checklist, true),
                    'technician_id' => !empty($devData['technician_id']) ? $devData['technician_id'] : null,
                    'status' => 'pending',
                    'labor_cost' => $labor,
                    'parts_cost' => 0,
                    'total_cost' => $labor,
                    'warranty_days' => intval($devData['warranty_days'] ?? 0),
                ]);

                // Attach spare parts if selected
                if (!empty($devData['parts']) && is_array($devData['parts'])) {
                    foreach ($devData['parts'] as $partData) {
                        if (empty($partData['product_id'])) continue;

                        $product = Product::find($partData['product_id']);
                        if (!$product) continue;

                        $qty = intval($partData['quantity'] ?? 1);
                        $unitPrice = floatval($partData['unit_price'] ?? $product->sale_price);
                        $subtotal = round($qty * $unitPrice, 2);

                        RepairDevicePart::create([
                            'repair_device_id' => $device->id,
                            'product_id' => $product->id,
                            'warehouse_id' => $partData['warehouse_id'] ?? $request->default_warehouse_id,
                            'quantity' => $qty,
                            'unit_cost' => 0,
                            'unit_price' => $unitPrice,
                            'subtotal' => $subtotal,
                            'is_deducted' => false,
                        ]);

                        $devicePartsCost += $subtotal;
                    }
                }

                $device->parts_cost = $devicePartsCost;
                $device->total_cost = $devicePartsCost + $labor;
                $device->save();

                $totalPartsCost += $devicePartsCost;
                $totalLaborCost += $labor;
            }

            $grandTotal = $totalPartsCost + $totalLaborCost;
            $balance = max(0, $grandTotal - $advance);

            $order->update([
                'total_parts_cost' => $totalPartsCost,
                'total_labor_cost' => $totalLaborCost,
                'total_amount' => $grandTotal,
                'balance_amount' => $balance,
            ]);

            // If advance amount paid at intake, record LedgerEntry into Accounts & Party Ledger
            if ($advance > 0) {
                $paymentAccName = in_array(strtolower($request->payment_method ?? 'cash'), ['bank', 'jazzcash', 'online']) ? 'Bank' : 'Cash';
                $paymentAccount = ChartOfAccount::where('name', $paymentAccName)->first() ?? ChartOfAccount::where('type', 'Asset')->first();
                $arAccount = ChartOfAccount::where('name', 'Accounts Receivable')->first();

                $walkInId = Setting::value('walk_in_customer_id') ?? Party::where('type', 'customer')->where('status', 1)->value('id');
                $effectivePartyId = $partyId ?: $walkInId;

                if ($paymentAccount && $arAccount) {
                    // Debit Cash / Bank Account (Money in)
                    LedgerEntry::create([
                        'account_id' => $paymentAccount->id,
                        'party_id' => null,
                        'warehouse_id' => $request->default_warehouse_id,
                        'debit' => $advance,
                        'credit' => 0,
                        'date' => $receivedDate->toDateString(),
                        'reference_type' => 'repair',
                        'reference_id' => $order->id,
                        'note' => "Advance received for Repair Order #{$order->order_no}",
                    ]);

                    // Credit Customer Accounts Receivable
                    LedgerEntry::create([
                        'account_id' => $arAccount->id,
                        'party_id' => $effectivePartyId,
                        'warehouse_id' => $request->default_warehouse_id,
                        'debit' => 0,
                        'credit' => $advance,
                        'date' => $receivedDate->toDateString(),
                        'reference_type' => 'repair',
                        'reference_id' => $order->id,
                        'note' => "Advance received for Repair Order #{$order->order_no}",
                    ]);
                }
            }

            $autoPrint = $request->has('auto_print') ? $request->boolean('auto_print') : true;

            $redirect = redirect()->route('repairs.show', [
                'repair' => $order->id,
                'auto_print' => $autoPrint ? 1 : null,
            ])->with('success', "Repair Order #{$order->order_no} kamyabi se jama ho gaya!");

            if ($autoPrint) {
                $redirect->with('auto_print', true)
                         ->with('auto_print_combo', route('repairs.print-combo', $order->id))
                         ->with('auto_print_stickers', route('repairs.stickers', $order->id))
                         ->with('auto_print_receipt', route('repairs.receipt', $order->id));
            }

            return $redirect;
        });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Repair order store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Repair order save karte waqt masla pesh aya: ' . $e->getMessage());
        }
    }

    /**
     * Show repair order details, devices, parts, and action controls.
     */
    public function show(RepairOrder $repair)
    {
        $repair->load([
            'customer',
            'creator',
            'devices.technician',
            'devices.parts.product',
            'devices.parts.warehouse'
        ]);

        $technicians = User::orderBy('name')->get(['id', 'name']);
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);

        return view('repairs.show', compact('repair', 'technicians', 'warehouses'));
    }

    /**
     * Show edit form for repair order (modify parts, labor, devices).
     */
    public function edit(RepairOrder $repair)
    {
        $repair->load([
            'devices.parts.product',
            'devices.technician',
            'customer'
        ]);

        $customers = Party::where('type', 'customer')->where('status', 1)->orderBy('name')->get()->map(function($c) {
            $c->current_balance = $c->getCurrentBalance();
            return $c;
        });
        $products = Product::where('status', 1)
            ->with(['stocks'])
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                $p->total_stock = (float) $p->stocks->sum('qty');
                $p->warehouse_stocks = $p->stocks->pluck('qty', 'warehouse_id');
                return $p;
            });
            
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);
        $technicians = User::orderBy('name')->get(['id', 'name']);
        $defaultWarehouse = $warehouses->first();

        // Load catalog brands with models & catalog services
        $brands = RepairBrand::where('status', 1)->with(['models' => function($q) {
            $q->where('status', 1)->orderBy('series')->orderBy('sort_order')->orderBy('name');
        }])->orderBy('sort_order')->orderBy('name')->get();

        $services = RepairService::where('status', 1)->orderBy('sort_order')->orderBy('category')->get();

        // Format initial devices for frontend Alpine.js
        $initialDevices = $repair->devices->map(function ($d) {
            return [
                'id' => $d->id,
                'trackingNo' => $d->device_tracking_no,
                'brand' => $d->brand ?? '',
                'model' => $d->model,
                'imei' => $d->imei ?? '',
                'color' => $d->color ?? '',
                'problem' => $d->problem_description,
                'services' => is_array($d->services) ? $d->services : (json_decode($d->services, true) ?? []),
                'securityType' => $d->security_type,
                'securityCode' => $d->security_code ?? '',
                'labor' => (float) $d->labor_cost,
                'technicianId' => $d->technician_id ?? '',
                'warrantyDays' => $d->warranty_days ?? 7,
                'parts' => $d->parts->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'productId' => $p->product_id,
                        'search' => $p->product ? $p->product->name : '',
                        'open' => false,
                        'highlightedIndex' => 0,
                        'qty' => $p->quantity,
                        'price' => (float) $p->unit_price,
                        'stock' => (float) ($p->product && $p->product->stocks ? $p->product->stocks->sum('qty') : 0),
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return view('repairs.edit', compact('repair', 'customers', 'products', 'warehouses', 'technicians', 'defaultWarehouse', 'initialDevices', 'brands', 'services'));
    }

    /**
     * Update repair order, devices, labor charges, and spare parts.
     */
    public function update(Request $request, RepairOrder $repair)
    {
        $request->validate([
            'customer_name' => 'required|string|min:2|max:255',
            'customer_phone' => 'required|string|min:7|max:50',
            'received_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'advance_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'devices' => 'required|array|min:1',
            'devices.*.model' => 'required|string|max:255',
            'devices.*.problem_description' => 'required|string',
            'devices.*.labor_cost' => 'nullable|numeric|min:0',
            'devices.*.warranty_days' => 'nullable|integer|min:0|max:365',
            'devices.*.parts.*.product_id' => 'nullable|exists:products,id',
            'devices.*.parts.*.quantity' => 'nullable|integer|min:1',
            'devices.*.parts.*.unit_price' => 'nullable|numeric|min:0',
        ], [
            'customer_name.required' => 'Customer ka naam likhna lazmi hai.',
            'customer_name.min' => 'Customer ka naam kam az kam 2 huroof ka hona chahiye.',
            'customer_phone.required' => 'Customer ka mobile / WhatsApp number lazmi hai.',
            'customer_phone.min' => 'Customer ka phone number kam az kam 7 digits ka hona chahiye.',
            'devices.required' => 'Kam az kam 1 mobile hona lazmi hai.',
            'devices.min' => 'Kam az kam 1 mobile hona lazmi hai.',
            'devices.*.model.required' => 'Mobile ka Model likhna lazmi hai.',
            'devices.*.problem_description.required' => 'Mobile ka masla / fault likhna lazmi hai.',
            'devices.*.labor_cost.numeric' => 'Labor charges sirf number hone chahiye.',
            'devices.*.parts.*.quantity.min' => 'Spare part ki quantity kam az kam 1 honi chahiye.',
            'devices.*.parts.*.unit_price.min' => 'Spare part ki price 0 ya is se zyada honi chahiye.',
        ]);

        try {
            return DB::transaction(function () use ($request, $repair) {
            $partyId = $request->party_id;
            if (!$partyId && $request->filled('customer_phone')) {
                $existingParty = Party::where('phone', $request->customer_phone)->first();
                if ($existingParty) {
                    $partyId = $existingParty->id;
                }
            }

            $advance = floatval($request->advance_amount ?? 0);
            $receivedDate = $request->filled('received_date') ? Carbon::parse($request->received_date) : $repair->received_date;

            $repair->update([
                'party_id' => $partyId,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'received_date' => $receivedDate,
                'expected_delivery_date' => $request->expected_delivery_date ? Carbon::parse($request->expected_delivery_date) : null,
                'total_devices' => count($request->devices),
                'advance_amount' => $advance,
                'payment_method' => $request->payment_method ?? $repair->payment_method,
                'notes' => $request->notes,
            ]);

            $totalPartsCost = 0;
            $totalLaborCost = 0;
            $existingDeviceIds = [];
            $deviceIndex = 1;

            foreach ($request->devices as $devData) {
                $labor = floatval($devData['labor_cost'] ?? 0);
                $devicePartsCost = 0;
                $checklist = $devData['condition_checklist'] ?? [];

                if (!empty($devData['id']) && $existingDev = RepairDevice::where('id', $devData['id'])->where('repair_order_id', $repair->id)->first()) {
                    $device = $existingDev;
                    $device->update([
                        'brand' => $devData['brand'] ?? null,
                        'model' => $devData['model'],
                        'imei' => $devData['imei'] ?? null,
                        'color' => $devData['color'] ?? null,
                        'security_type' => $devData['security_type'] ?? 'none',
                        'security_code' => $devData['security_code'] ?? null,
                        'problem_description' => $devData['problem_description'],
                        'services' => !empty($devData['services']) ? (is_array($devData['services']) ? $devData['services'] : json_decode($devData['services'], true)) : null,
                        'condition_checklist' => is_array($checklist) ? $checklist : json_decode($checklist, true),
                        'technician_id' => !empty($devData['technician_id']) ? $devData['technician_id'] : null,
                        'labor_cost' => $labor,
                        'warranty_days' => intval($devData['warranty_days'] ?? 0),
                    ]);
                } else {
                    $trackingNo = $repair->order_no . '-' . ($repair->devices()->count() + $deviceIndex++);
                    $device = RepairDevice::create([
                        'repair_order_id' => $repair->id,
                        'device_tracking_no' => $trackingNo,
                        'brand' => $devData['brand'] ?? null,
                        'model' => $devData['model'],
                        'imei' => $devData['imei'] ?? null,
                        'color' => $devData['color'] ?? null,
                        'security_type' => $devData['security_type'] ?? 'none',
                        'security_code' => $devData['security_code'] ?? null,
                        'problem_description' => $devData['problem_description'],
                        'services' => !empty($devData['services']) ? (is_array($devData['services']) ? $devData['services'] : json_decode($devData['services'], true)) : null,
                        'accessories_received' => $devData['accessories_received'] ?? null,
                        'condition_checklist' => is_array($checklist) ? $checklist : json_decode($checklist, true),
                        'technician_id' => !empty($devData['technician_id']) ? $devData['technician_id'] : null,
                        'status' => 'pending',
                        'labor_cost' => $labor,
                        'parts_cost' => 0,
                        'total_cost' => $labor,
                        'warranty_days' => intval($devData['warranty_days'] ?? 0),
                    ]);
                }

                $existingDeviceIds[] = $device->id;

                // Sync spare parts: remove old undeducted parts and re-create/update
                $existingPartIds = [];
                if (!empty($devData['parts']) && is_array($devData['parts'])) {
                    foreach ($devData['parts'] as $partData) {
                        if (empty($partData['product_id'])) continue;
                        $product = Product::find($partData['product_id']);
                        if (!$product) continue;

                        $qty = intval($partData['quantity'] ?? 1);
                        $unitPrice = floatval($partData['unit_price'] ?? $product->sale_price);
                        $subtotal = round($qty * $unitPrice, 2);

                        if (!empty($partData['id']) && $existingPart = RepairDevicePart::where('id', $partData['id'])->where('repair_device_id', $device->id)->first()) {
                            $existingPart->update([
                                'product_id' => $product->id,
                                'quantity' => $qty,
                                'unit_price' => $unitPrice,
                                'subtotal' => $subtotal,
                            ]);
                            $existingPartIds[] = $existingPart->id;
                        } else {
                            $newPart = RepairDevicePart::create([
                                'repair_device_id' => $device->id,
                                'product_id' => $product->id,
                                'warehouse_id' => $partData['warehouse_id'] ?? $request->default_warehouse_id,
                                'quantity' => $qty,
                                'unit_cost' => 0,
                                'unit_price' => $unitPrice,
                                'subtotal' => $subtotal,
                                'is_deducted' => false,
                            ]);
                            $existingPartIds[] = $newPart->id;
                        }

                        $devicePartsCost += $subtotal;
                    }
                }

                // Delete any parts removed by user (only if not yet deducted)
                RepairDevicePart::where('repair_device_id', $device->id)
                    ->where('is_deducted', false)
                    ->whereNotIn('id', $existingPartIds)
                    ->delete();

                $device->parts_cost = $devicePartsCost;
                $device->total_cost = $devicePartsCost + $labor;
                $device->save();

                $totalPartsCost += $devicePartsCost;
                $totalLaborCost += $labor;
            }

            // Remove any devices deleted by user
            RepairDevice::where('repair_order_id', $repair->id)
                ->whereNotIn('id', $existingDeviceIds)
                ->delete();

            $grandTotal = $totalPartsCost + $totalLaborCost;
            $balance = max(0, $grandTotal - $advance);

            $repair->update([
                'total_parts_cost' => $totalPartsCost,
                'total_labor_cost' => $totalLaborCost,
                'total_amount' => $grandTotal,
                'balance_amount' => $balance,
            ]);

            // Update advance in ledger if advance changed
            $advanceEntries = LedgerEntry::where('reference_type', 'repair')
                ->where('reference_id', $repair->id)
                ->where('note', 'like', 'Advance%')
                ->get();

            if ($advanceEntries->count() > 0) {
                if ($advance > 0) {
                    foreach ($advanceEntries as $entry) {
                        if ($entry->debit > 0) {
                            $entry->update(['debit' => $advance, 'date' => $receivedDate->toDateString()]);
                        } elseif ($entry->credit > 0) {
                            $walkInId = Setting::value('walk_in_customer_id') ?? Party::where('type', 'customer')->where('status', 1)->value('id');
                            $entry->update(['credit' => $advance, 'party_id' => $partyId ?: $walkInId, 'date' => $receivedDate->toDateString()]);
                        }
                    }
                } else {
                    $advanceEntries->each->delete();
                }
            } elseif ($advance > 0) {
                // Advance was newly added in edit
                $paymentAccName = in_array(strtolower($request->payment_method ?? 'cash'), ['bank', 'jazzcash', 'online']) ? 'Bank' : 'Cash';
                $paymentAccount = ChartOfAccount::where('name', $paymentAccName)->first() ?? ChartOfAccount::where('type', 'Asset')->first();
                $arAccount = ChartOfAccount::where('name', 'Accounts Receivable')->first();
                $walkInId = Setting::value('walk_in_customer_id') ?? Party::where('type', 'customer')->where('status', 1)->value('id');
                $effectivePartyId = $partyId ?: $walkInId;

                if ($paymentAccount && $arAccount) {
                    LedgerEntry::create([
                        'account_id' => $paymentAccount->id,
                        'party_id' => null,
                        'warehouse_id' => $request->default_warehouse_id,
                        'debit' => $advance,
                        'credit' => 0,
                        'date' => $receivedDate->toDateString(),
                        'reference_type' => 'repair',
                        'reference_id' => $repair->id,
                        'note' => "Advance received for Repair Order #{$repair->order_no}",
                    ]);

                    LedgerEntry::create([
                        'account_id' => $arAccount->id,
                        'party_id' => $effectivePartyId,
                        'warehouse_id' => $request->default_warehouse_id,
                        'debit' => 0,
                        'credit' => $advance,
                        'date' => $receivedDate->toDateString(),
                        'reference_type' => 'repair',
                        'reference_id' => $repair->id,
                        'note' => "Advance received for Repair Order #{$repair->order_no}",
                    ]);
                }
            }

            return redirect()->route('repairs.show', $repair->id)
                ->with('success', "Repair Order #{$repair->order_no} updated successfully! Parts, labor charges, and balance recalculations saved.");
        });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Repair order update error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Repair order update karte waqt masla pesh aya: ' . $e->getMessage());
        }
    }

    /**
     * Update individual device status (e.g. diagnosing, waiting_parts, completed, unrepairable).
     */
    public function updateDeviceStatus(Request $request, RepairDevice $device)
    {
        $request->validate([
            'status' => 'required|in:pending,diagnosing,waiting_parts,completed,delivered,unrepairable',
            'technician_id' => 'nullable|exists:users,id',
        ]);

        $device->status = $request->status;
        if ($request->filled('technician_id')) {
            $device->technician_id = $request->technician_id;
        }

        if ($request->status === 'completed' && !$device->completed_at) {
            $device->completed_at = now();
        }

        $device->save();

        // Update parent order status
        $order = $device->repairOrder;
        $deviceStatuses = $order->devices()->pluck('status')->toArray();

        if (collect($deviceStatuses)->every(fn($s) => $s === 'delivered')) {
            $order->status = 'delivered';
        } elseif (collect($deviceStatuses)->every(fn($s) => in_array($s, ['completed', 'delivered']))) {
            $order->status = 'completed';
        } elseif (collect($deviceStatuses)->contains(fn($s) => in_array($s, ['in_progress', 'diagnosing', 'waiting_parts']))) {
            $order->status = 'in_progress';
        } else {
            $order->status = 'partially_completed';
        }
        $order->save();

        return back()->with('success', "Device {$device->device_tracking_no} status updated to " . ucfirst(str_replace('_', ' ', $request->status)) . "!");
    }

    /**
     * Mark order and devices as delivered, receive remaining balance, and deduct inventory.
     */
    public function deliver(Request $request, RepairOrder $repair)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'default_warehouse_id' => 'required|exists:warehouses,id',
        ]);

        return DB::transaction(function () use ($request, $repair) {
            $paidNow = floatval($request->paid_amount);
            $newAdvance = $repair->advance_amount + $paidNow;
            $newBalance = max(0, $repair->total_amount - $newAdvance);

            $repair->update([
                'advance_amount' => $newAdvance,
                'balance_amount' => $newBalance,
                'status' => 'delivered',
            ]);

            $effectiveWarehouseId = $request->default_warehouse_id;
            $totalPartsCogs = 0;

            // Mark devices delivered and deduct stock for parts
            foreach ($repair->devices as $device) {
                if ($device->status !== 'delivered') {
                    $device->status = 'delivered';
                    $device->delivered_at = now();
                    $device->save();
                }

                // Deduct spare parts stock & calculate cost
                foreach ($device->parts as $part) {
                    if (!$part->is_deducted) {
                        $warehouseId = $part->warehouse_id ?? $effectiveWarehouseId;
                        $stock = Stock::where('product_id', $part->product_id)
                            ->where('warehouse_id', $warehouseId)
                            ->lockForUpdate()
                            ->first();

                        $avgCost = $stock ? (float) $stock->avg_cost : 0;
                        $part->unit_cost = $avgCost;
                        $totalPartsCogs += round($part->quantity * $avgCost, 2);

                        if ($stock) {
                            $stock->qty -= $part->quantity;
                            $stock->save();
                        } else {
                            Stock::create([
                                'product_id' => $part->product_id,
                                'warehouse_id' => $warehouseId,
                                'qty' => -$part->quantity,
                                'avg_cost' => 0,
                            ]);
                        }

                        $part->is_deducted = true;
                        $part->save();
                    }
                }
            }

            // Post Double Entry Accounting Entries
            $repairIncomeAccount = ChartOfAccount::firstOrCreate(
                ['name' => 'Repair Services Income'],
                ['type' => 'Income', 'opening_balance' => 0, 'balance_type' => 'credit', 'status' => 1]
            );
            $arAccount = ChartOfAccount::where('name', 'Accounts Receivable')->first();
            $cogsAccount = ChartOfAccount::where('name', 'COGS')->first();
            $inventoryAccount = ChartOfAccount::where('name', 'Inventory')->first();

            $paymentAccName = in_array(strtolower($request->payment_method ?? 'cash'), ['bank', 'jazzcash', 'online']) ? 'Bank' : 'Cash';
            $paymentAccount = ChartOfAccount::where('name', $paymentAccName)->first() ?? ChartOfAccount::where('type', 'Asset')->first();

            $deliveryDate = now()->toDateString();
            $walkInId = Setting::value('walk_in_customer_id') ?? Party::where('type', 'customer')->where('status', 1)->value('id');
            $customerPartyId = $repair->party_id ?: $walkInId;

            // 1. Recognize Total Repair Revenue (Debit Customer AR, Credit Repair Services Income)
            if ($arAccount && $repairIncomeAccount && $repair->total_amount > 0) {
                // Debit Customer (AR)
                LedgerEntry::create([
                    'account_id' => $arAccount->id,
                    'party_id' => $customerPartyId,
                    'warehouse_id' => $effectiveWarehouseId,
                    'debit' => $repair->total_amount,
                    'credit' => 0,
                    'date' => $deliveryDate,
                    'reference_type' => 'repair',
                    'reference_id' => $repair->id,
                    'note' => "Repair Service Charges for Order #{$repair->order_no}",
                ]);

                // Credit Repair Services Income
                LedgerEntry::create([
                    'account_id' => $repairIncomeAccount->id,
                    'party_id' => null,
                    'warehouse_id' => $effectiveWarehouseId,
                    'debit' => 0,
                    'credit' => $repair->total_amount,
                    'date' => $deliveryDate,
                    'reference_type' => 'repair',
                    'reference_id' => $repair->id,
                    'note' => "Repair Revenue for Order #{$repair->order_no}",
                ]);
            }

            // 2. Record Payment Received at Delivery
            if ($paidNow > 0 && $paymentAccount && $arAccount) {
                // Debit Cash / Bank Account (Money In)
                LedgerEntry::create([
                    'account_id' => $paymentAccount->id,
                    'party_id' => null,
                    'warehouse_id' => $effectiveWarehouseId,
                    'debit' => $paidNow,
                    'credit' => 0,
                    'date' => $deliveryDate,
                    'reference_type' => 'repair',
                    'reference_id' => $repair->id,
                    'note' => "Delivery payment for Repair #{$repair->order_no}",
                ]);

                // Credit Customer (AR)
                LedgerEntry::create([
                    'account_id' => $arAccount->id,
                    'party_id' => $customerPartyId,
                    'warehouse_id' => $effectiveWarehouseId,
                    'debit' => 0,
                    'credit' => $paidNow,
                    'date' => $deliveryDate,
                    'reference_type' => 'repair',
                    'reference_id' => $repair->id,
                    'note' => "Delivery payment for Repair #{$repair->order_no}",
                ]);
            }

            // 3. COGS & Inventory Reduction Entry for Parts
            if ($totalPartsCogs > 0 && $cogsAccount && $inventoryAccount) {
                // Debit COGS
                LedgerEntry::create([
                    'account_id' => $cogsAccount->id,
                    'party_id' => null,
                    'warehouse_id' => $effectiveWarehouseId,
                    'debit' => $totalPartsCogs,
                    'credit' => 0,
                    'date' => $deliveryDate,
                    'reference_type' => 'repair',
                    'reference_id' => $repair->id,
                    'note' => "COGS for Repair Parts in Order #{$repair->order_no}",
                ]);

                // Credit Inventory
                LedgerEntry::create([
                    'account_id' => $inventoryAccount->id,
                    'party_id' => null,
                    'warehouse_id' => $effectiveWarehouseId,
                    'debit' => 0,
                    'credit' => $totalPartsCogs,
                    'date' => $deliveryDate,
                    'reference_type' => 'repair',
                    'reference_id' => $repair->id,
                    'note' => "Inventory reduction for Repair Parts in Order #{$repair->order_no}",
                ]);
            }

            return back()->with('success', "Repair Order #{$repair->order_no} marked as DELIVERED! Payment, accounts ledger, and inventory stock updated successfully.");
        });
    }

    /**
     * Combined Slip: Print Mobile Sticker (Slip 1) + Customer Invoice/Receipt (Slip 2) together in one go.
     * In one print action:
     * - Slip 1: Mobile Sticker (to stick on mobile back)
     * - Slip 2: Customer Copy / Claim Invoice (given to customer)
     */
    public function printCombo(RepairOrder $repair)
    {
        $repair->load(['devices.parts.product', 'devices.technician', 'customer', 'creator']);
        $settings = Setting::first();
        $devices = $repair->devices;

        return view('repairs.print-combo', compact('repair', 'devices', 'settings'));
    }

    /**
     * Slip 1: Print Mobile Tag / Sticker Slips (Mobile Ke Peechay Chipkane Wali)
     */
    public function printStickers(RepairOrder $repair, Request $request)
    {
        $repair->load(['devices.technician', 'customer']);
        $settings = Setting::first();
        
        // Single device or all devices
        $devices = $repair->devices;
        if ($request->filled('device_id')) {
            $devices = $repair->devices->where('id', $request->device_id);
        }

        return view('repairs.print-stickers', compact('repair', 'devices', 'settings'));
    }

    /**
     * Slip 2: Print Customer Intake Receipt (Jo Customer Ghar Le Kar Jayega)
     */
    public function printReceipt(RepairOrder $repair)
    {
        $repair->load(['devices.parts.product', 'customer', 'creator']);
        $settings = Setting::first();

        return view('repairs.print-receipt', compact('repair', 'settings'));
    }

    /**
     * Dedicated Repair Catalog Settings (Brands, Models & Services).
     */
    public function catalog(Request $request)
    {
        $brands = RepairBrand::withCount('models')
            ->with(['models' => function ($q) {
                $q->orderBy('series')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $services = RepairService::orderBy('category')->orderBy('sort_order')->orderBy('name')->get();

        $categories = RepairService::select('category')->distinct()->pluck('category')->filter()->values();
        if ($categories->isEmpty()) {
            $categories = collect(['Screen & Display', 'Battery & Charging', 'Camera & Audio', 'Board & Motherboard', 'Software & OS', 'Body & Housing', 'Other Services']);
        }

        return view('repairs.catalog', compact('brands', 'services', 'categories'));
    }

    /**
     * Quick or Form store for new Repair Service.
     */
    public function storeService(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'nullable|string|max:100',
            'default_labor_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $service = RepairService::firstOrCreate(
            ['name' => trim($request->name)],
            [
                'category' => trim($request->category ?: 'General Repair'),
                'default_labor_cost' => floatval($request->default_labor_cost ?? 0),
                'description' => $request->description,
                'status' => true,
                'sort_order' => (RepairService::max('sort_order') ?? 0) + 1,
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'service' => $service,
                'message' => 'Nayi service kamiyabi se add ho gayi.'
            ]);
        }

        return redirect()->route('repairs.catalog')->with('success', 'Nayi service "' . $service->name . '" shamil kar di gayi hai.');
    }

    /**
     * Update existing Repair Service.
     */
    public function updateService(Request $request, RepairService $service)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'nullable|string|max:100',
            'default_labor_cost' => 'nullable|numeric|min:0',
        ]);

        $service->update([
            'name' => trim($request->name),
            'category' => trim($request->category ?: 'General Repair'),
            'default_labor_cost' => floatval($request->default_labor_cost ?? 0),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'service' => $service,
                'message' => 'Service update ho gayi.'
            ]);
        }

        return redirect()->route('repairs.catalog')->with('success', 'Service "' . $service->name . '" update ho gayi hai.');
    }

    /**
     * Delete Repair Service.
     */
    public function destroyService(RepairService $service)
    {
        $name = $service->name;
        $service->delete();
        return redirect()->route('repairs.catalog')->with('success', 'Service "' . $name . '" delete kar di gayi hai.');
    }

    /**
     * Quick AJAX or Form store for new Repair Brand.
     */
    public function storeBrand(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $name = trim($request->name);
        $brand = RepairBrand::firstOrCreate(
            ['name' => $name],
            ['status' => true, 'sort_order' => (RepairBrand::max('sort_order') ?? 0) + 1]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'brand' => $brand->load('models'),
            ]);
        }

        return redirect()->route('repairs.catalog')->with('success', 'Brand "' . $brand->name . '" shamil kar diya gaya hai.');
    }

    /**
     * Delete Repair Brand and its models.
     */
    public function destroyBrand(RepairBrand $brand)
    {
        $name = $brand->name;
        $brand->models()->delete();
        $brand->delete();
        return redirect()->route('repairs.catalog')->with('success', 'Brand "' . $name . '" aur uske models delete kar diye gaye hain.');
    }

    /**
     * Quick AJAX or Form store for new Repair Model under a Brand.
     */
    public function storeModel(Request $request)
    {
        $request->validate([
            'repair_brand_id' => 'required|exists:repair_brands,id',
            'name' => 'required|string|max:100',
            'series' => 'nullable|string|max:100',
        ]);

        $name = trim($request->name);
        $model = RepairModel::firstOrCreate(
            [
                'repair_brand_id' => $request->repair_brand_id,
                'name' => $name,
            ],
            [
                'series' => trim($request->series ?: 'Other Models'),
                'status' => true,
                'sort_order' => (RepairModel::where('repair_brand_id', $request->repair_brand_id)->max('sort_order') ?? 0) + 1,
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'model' => $model,
            ]);
        }

        return redirect()->route('repairs.catalog')->with('success', 'Model "' . $model->name . '" shamil kar diya gaya hai.');
    }

    /**
     * Delete Repair Model.
     */
    public function destroyModel(RepairModel $model)
    {
        $name = $model->name;
        $model->delete();
        return redirect()->route('repairs.catalog')->with('success', 'Model "' . $name . '" delete kar diya gaya hai.');
    }

    /**
     * Final Tax / Delivery Bill & Warranty Invoice
     */
    public function printInvoice(RepairOrder $repair)
    {
        $repair->load(['devices.parts.product', 'customer', 'creator']);
        $settings = Setting::first();

        return view('repairs.print-invoice', compact('repair', 'settings'));
    }
}
