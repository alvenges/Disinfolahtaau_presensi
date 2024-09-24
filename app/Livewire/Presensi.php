<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Schedule;
use App\Models\Leave;
use App\Models\Attendance;
use Auth;
use Illuminate\Support\Carbon;

class Presensi extends Component
{
    public $latitude;
    public $longitude;
    public $insideRadius = false;

    public function render()
    {
        $schedule = Schedule::where('user_id', Auth::user()->id)->first();
        $attendance = Attendance::where('user_id', Auth::user()->id)
                            ->whereDate('created_at', date('Y-m-d'))->first();
        return view('livewire.presensi', [
            'schedule' => $schedule,
            'insideRadius' => $this->insideRadius,
            'attendance' => $attendance
        ]);
    }

    public function store()
{
    // Validasi input
    $this->validate([
        'latitude' => 'required',
        'longitude' => 'required'
    ]);

    // Ambil jadwal berdasarkan user yang login
    $schedule = Schedule::where('user_id', Auth::user()->id)->first();

    $today = Carbon::today()->format('Y-m-d');

    // Periksa persetujuan cuti untuk user
    $approvedLeave = Leave::where('user_id', Auth::user()->id)
        ->where('status', 'approved')
        ->whereDate('start_date', '<=', $today)
        ->whereDate('end_date', '>=', $today)
        ->exists();

    if ($approvedLeave) {
        session()->flash('error', 'Anda tidak dapat melakukan presensi karena sedang cuti');
        return;
    }

    if ($schedule) {
        // Cek apakah sudah ada presensi hari ini
        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->whereDate('created_at', $today)
            ->first();

        if (!$attendance) {
            // Jika tidak ada, ini adalah presensi masuk
            $attendance = Attendance::create([
                'user_id' => Auth::user()->id,
                'schedule_latitude' => $schedule->office->latitude,
                'schedule_longitude' => $schedule->office->longitude,
                'schedule_start_time' => $schedule->shift->start_time,
                'schedule_end_time' => $schedule->shift->end_time,
                'start_latitude' => $this->latitude,
                'start_longitude' => $this->longitude,
                'start_time' => Carbon::now()->toTimeString(),
                'end_time' => null, // Set end_time sebagai NULL saat presensi masuk
                'status' => 'start' // Tandai ini sebagai presensi masuk
            ]);
        } else {
            // Jika sudah ada, ini adalah presensi akhir (pulang)
            $attendance->update([
                'end_latitude' => $this->latitude,
                'end_longitude' => $this->longitude,
                'end_time' => Carbon::now()->toTimeString(), // Set end_time saat pulang
                'status' => 'end' // Tandai ini sebagai presensi pulang
            ]);
        }

        // Redirect setelah proses berhasil
        return redirect('admin/attendances');


        
        }
    }
}

