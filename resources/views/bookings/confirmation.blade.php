@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4">
        {{-- Invoice Wrapper --}}
        <div class="bg-white shadow-2xl rounded-sm overflow-hidden border border-gray-200" id="printable-invoice">
            
            {{-- Invoice Header --}}
            <div class="p-8 border-b-2 border-[#003B95] flex justify-between items-start">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full border-4 border-[#003B95] flex items-center justify-center">
                        <svg class="w-12 h-12 text-[#003B95]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 11a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-[#003B95] tracking-tighter">COASTAL CHARMZ</h1>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mt-1">Luxury Stays & Experiences</p>
                    </div>
                </div>
                
                <div class="text-right text-gray-600 space-y-1">
                    <p class="text-sm font-bold flex items-center justify-end gap-2"><i class="fa-solid fa-phone text-xs"></i> +1 9899 998 999</p>
                    <p class="text-sm font-bold flex items-center justify-end gap-2"><i class="fa-solid fa-location-dot text-xs"></i> Lutheran Avenue, L.A</p>
                    <p class="text-sm font-bold flex items-center justify-end gap-2"><i class="fa-solid fa-envelope text-xs"></i> hello@coastalcharmz.com</p>
                    <p class="text-sm font-bold flex items-center justify-end gap-2"><i class="fa-solid fa-globe text-xs"></i> www.coastalcharmz.com</p>
                </div>
            </div>

            <div class="p-8">
                <div class="flex justify-between items-end mb-10">
                    <div>
                        <h2 class="text-lg font-black text-gray-900 border-b-2 border-gray-900 inline-block mb-4">CLIENT DETAILS:</h2>
                        <div class="space-y-1">
                            <p class="text-sm font-bold text-gray-800">{{ strtoupper($booking->user->name) }}</p>
                            <p class="text-sm text-gray-600">{{ $booking->user->email }}</p>
                            <p class="text-sm text-gray-600">{{ $booking->user->phone ?? 'No phone provided' }}</p>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <span class="bg-[#003B95] text-white text-xl font-black px-6 py-2 rounded-md mb-4 inline-block">INVOICE</span>
                        <div class="space-y-2 mt-4 text-sm font-bold">
                            <p class="text-gray-500 uppercase">Date: <span class="text-gray-900 border-b border-gray-300 ml-2 pb-1">{{ now()->format('M d, Y') }}</span></p>
                            <p class="text-gray-500 uppercase">No: <span class="text-gray-900 border-b border-gray-300 ml-2 pb-1">#INV-{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</span></p>
                        </div>
                    </div>
                </div>

                {{-- Items Table --}}
                <table class="w-full border-collapse mb-8">
                    <thead>
                        <tr class="border-2 border-gray-900 text-sm font-black uppercase text-center bg-gray-50">
                            <th class="border-r-2 border-gray-900 p-3 w-16">No.</th>
                            <th class="border-r-2 border-gray-900 p-3 text-left">Item Description</th>
                            <th class="border-r-2 border-gray-900 p-3 w-24">Qty</th>
                            <th class="border-r-2 border-gray-900 p-3 w-32">Price</th>
                            <th class="p-3 w-32 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="border-2 border-gray-900">
                        <tr class="h-64 align-top">
                            <td class="border-r-2 border-gray-900 p-4 text-center text-sm font-bold">01</td>
                            <td class="border-r-2 border-gray-900 p-4">
                                <p class="text-sm font-black text-gray-900 mb-1">{{ strtoupper($booking->apartment->name) }}</p>
                                <p class="text-xs text-gray-600 font-medium">Booking Period: {{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500 mt-2 italic">* Booking status: {{ strtoupper($booking->status) }}</p>
                            </td>
                            <td class="border-r-2 border-gray-900 p-4 text-center text-sm font-bold">{{ $booking->nights }} Nights</td>
                            <td class="border-r-2 border-gray-900 p-4 text-center text-sm font-bold">${{ number_format($booking->apartment->price_per_night) }}</td>
                            <td class="p-4 text-right text-sm font-black">${{ number_format($booking->total_price) }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Footer Details --}}
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-sm font-black text-gray-900 border-b-2 border-gray-900 inline-block mb-4">PAYMENT DETAILS:</h2>
                        <div class="space-y-1 text-xs">
                            <p class="text-gray-500 font-bold uppercase">Acc Name: <span class="text-gray-900 ml-2">Coastal Charmz Stays</span></p>
                            <p class="text-gray-500 font-bold uppercase">Acc Number: <span class="text-gray-900 ml-2">56933366566</span></p>
                            <p class="text-gray-500 font-bold uppercase">Bank Name: <span class="text-gray-900 ml-2">Bank of Coastal L.A</span></p>
                            <p class="text-gray-500 font-bold uppercase">Branch: <span class="text-gray-900 ml-2">Main Avenue</span></p>
                        </div>
                    </div>
                    
                    <div class="w-64">
                        <div class="border-2 border-gray-900">
                            <div class="flex border-b-2 border-gray-900">
                                <div class="w-1/2 p-2 text-sm font-black uppercase text-center border-r-2 border-gray-900 bg-gray-50">Sub Total</div>
                                <div class="w-1/2 p-2 text-sm font-black text-right">${{ number_format($booking->total_price) }}</div>
                            </div>
                            <div class="flex border-b-2 border-gray-900">
                                <div class="w-1/2 p-2 text-sm font-black uppercase text-center border-r-2 border-gray-900 bg-gray-50">Taxes (0%)</div>
                                <div class="w-1/2 p-2 text-sm font-black text-right">$0.00</div>
                            </div>
                            <div class="flex bg-gray-50">
                                <div class="w-1/2 p-2 text-sm font-black uppercase text-center border-r-2 border-gray-900">Totals</div>
                                <div class="w-1/2 p-2 text-lg font-black text-right">${{ number_format($booking->total_price) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Note --}}
            <div class="bg-[#003973] text-white text-center py-3 font-black text-sm uppercase italic tracking-widest mt-8">
                THANK YOU FOR THE BUSINESS!
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-8 flex gap-4 print:hidden">
            <button onclick="window.print()" class="flex-1 bg-white border-2 border-gray-900 text-gray-900 font-black py-4 px-6 rounded-md hover:bg-gray-50 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-print"></i> PRINT INVOICE
            </button>
            <a href="{{ route('dashboard') }}" class="flex-1 bg-[#003B95] text-white font-black py-4 px-6 rounded-md hover:bg-[#002b6d] transition text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-house"></i> GO TO DASHBOARD
            </a>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-invoice, #printable-invoice * {
            visibility: visible;
        }
        #printable-invoice {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none;
            border: none;
        }
        .print\:hidden {
            display: none !important;
        }
    </div>
</style>
@endsection

