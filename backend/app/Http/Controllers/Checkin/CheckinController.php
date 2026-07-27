<?php

namespace App\Http\Controllers\Checkin;

use App\Http\Controllers\Controller;
use App\Domains\Checkin\Models\Checkin;
use App\Events\CheckinVerified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CheckinController extends Controller
{
    public function index()
    {
        $checkins = Checkin::where('tenant_id', tenant_id())
            ->with(['reservation.property'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('panels.checkins.index', compact('checkins'));
    }

    public function show(Checkin $checkin)
    {
        $this->authorize('view', $checkin);
        $checkin->load(['reservation.property', 'reservation.guests', 'guestDocuments', 'verifiedBy']);
        return view('panels.checkins.show', compact('checkin'));
    }

    public function verify(Request $request, Checkin $checkin)
    {
        $this->authorize('verify', $checkin);

        $request->validate(['action' => 'required|in:verify,reject']);

        $checkin->update([
            'status' => $request->action === 'verify' ? 'verified' : 'rejected',
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        if ($request->action === 'verify') {
            event(new CheckinVerified($checkin));
        }

        return redirect()->back()->with('success', 'Check-in ' . ($request->action === 'verify' ? 'verificado' : 'rechazado'));
    }

    public function destroy(Request $request, Checkin $checkin)
    {
        $this->authorize('delete', $checkin);

        foreach ($checkin->guestDocuments as $doc) {
            Storage::disk($doc->disk)->delete($doc->path);
            $doc->forceDelete();
        }

        $checkin->sesSubmissions()->delete();
        $checkin->delete();

        return redirect()->route('checkins.index')
            ->with('success', 'Check-in eliminado');
    }
}
