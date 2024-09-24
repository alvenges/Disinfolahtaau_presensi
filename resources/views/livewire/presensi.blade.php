<div>
    <div class="container mx-auto max-w-sm">
        <div class="bg-white p-6 rounded-lg mt-3 shadow-lg">
            <div class="grid grid-cols-1 gap-6 mb-6">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Informasi Personel</h2>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <p><strong>Nama Personel :</strong> {{ Auth::user()->name }}</p>
                        <p><strong>Kantor :</strong> {{ $schedule ? $schedule->office->name : 'N/A' }}</p>
                        <p><strong>Shift :</strong> {{ $schedule ? $schedule->shift->name : 'N/A' }} ({{ $schedule ? $schedule->shift->start_time : 'N/A' }} - {{ $schedule ? $schedule->shift->end_time : 'N/A' }})</p>
                        @if($schedule && $schedule->dinas_luar)
                            <p class="text-green-500"><strong>Status : </strong><strong>Dinas Luar</strong></p>
                        @elseif($schedule)
                            <p class="text-green-500"><strong>Status : </strong><strong>Dinas Dalam</strong></p>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h4 class="text-l font-bold mb-2">Waktu Datang</h4>
                            <p><strong>{{ $attendance ? $attendance->start_time : '-' }}</strong></p>
                        </div>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h4 class="text-l font-bold mb-2">Waktu Pulang</h4>
                            <p><strong>{{ $attendance ? $attendance->end_time : '-' }}</strong></p>
                        </div>
                    </div> <!-- Penutupan tag div grid -->
                </div>

                <div>
                    <h2 class="text-2xl font-bold mb-2">Presensi</h2>
                    <div id="map" class="mb-4 rounded-lg border border-gray-300" wire:ignore></div>
                    @if (session()->has('error'))
                        <div style="color: red; padding: 10px; border; 1px solid red; background-color: #fdd;">
                            {{session('error')}}
                        </div> 
                    @endif
                    <form class="row g-3 mt-3" wire:submit.prevent="store" enctype="multipart/form-data">
                        <button type="button" onclick="tagLocation()" class="px-4 py-2 bg-blue-500 text-white rounded">Tag Location</button>
                        @if($insideRadius)
                            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">Submit Presensi</button>
                        @endif  
                    </form>                
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        let map;
        let lat;
        let lng;
        const office = [{{ $schedule ? $schedule->office->latitude : '0' }}, {{ $schedule ? $schedule->office->longitude : '0' }}];
        const radius = {{ $schedule ? $schedule->office->radius : '100' }};
        let component;
        let marker;

        document.addEventListener('livewire:initialized', function() {
            component = @this;
            map = L.map('map').setView(office, 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            const circle = L.circle(office, {
                color: 'blue',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: radius
            }).addTo(map);
        });

        function tagLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    lat = position.coords.latitude;
                    lng = position.coords.longitude;

                    if (marker) {
                        map.removeLayer(marker);
                    }

                    marker = L.marker([lat, lng]).addTo(map);
                    map.setView([lat, lng], 15);

                    if (isWithinRadius(lat, lng, office, radius)) {
                        component.set('insideRadius', true); 
                        component.set('latitude', lat);
                        component.set('longitude', lng);                  
                    }
                });
            } else {
                alert('Tidak Bisa Get Location');
            }
        }

        function isWithinRadius(lat, lng, center, radius) {
            if (radius <= 0) return false; // Fallback if radius is not defined correctly
            const dinas_luar = {{ $schedule ? ($schedule->dinas_luar ? 'true' : 'false') : 'false' }};
            if (dinas_luar){
                return true;
            } else {
                let distance = map.distance([lat, lng], center);
                return distance <= radius;
            }
        }
    </script>
</div>
