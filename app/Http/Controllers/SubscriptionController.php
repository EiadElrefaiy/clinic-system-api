<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionController extends Controller
{
    private function useMainDatabase()
    {
        Config::set('database.default', 'mysql'); 
        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    public function index()
    {
        $this->useMainDatabase();
        $subscriptions = Subscription::all();
        return response()->json(['subscriptions' => $subscriptions]);
    }

    public function show($id)
    {
        $this->useMainDatabase();
        $user = User::findOrFail($id);
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json(['error' => 'No subscription found'], 404);
        }

        return response()->json(['subscription' => $subscription]);
    }

    public function new(Request $request)
    {
        $this->useMainDatabase();
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'expiry_date' => 'required|date'
        ]);

        $user = User::findOrFail($request->user_id);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'license_number' => 'LIC-' . strtoupper(uniqid()),
            'expiry_date' => $request->expiry_date
        ]);

        return response()->json([
            'message' => 'Subscription created successfully',
            'subscription' => $subscription
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->useMainDatabase();
        $subscription = Subscription::findOrFail($id);

        $request->validate([
            'expiry_date' => 'required|date'
        ]);

        $subscription->update([
            'expiry_date' => $request->expiry_date
        ]);

        return response()->json([
            'message' => 'Subscription updated successfully',
            'subscription' => $subscription
        ]);
    }

    public function delete($id)
    {
        $this->useMainDatabase();
        $subscription = Subscription::findOrFail($id);
        $subscription->delete();

        return response()->json(['message' => 'Subscription deleted successfully']);
    }
}
