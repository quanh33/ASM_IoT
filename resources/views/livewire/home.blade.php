<div class="p-4 space-y-4">
    <h1 class="text-2xl font-bold text-primary">🌱 Smart IoT App</h1>
    <hr class="border-gray-300" />

    <!-- Sensor stats -->
    <div class="grid grid-cols-3 md:grid-cols-3 gap-4">
        <div class="stat shadow-lg border-t-4 border-orange-400">
            <div class="stat-title text-gray-600">Temperature:</div>
            <div class="stat-value text-orange-500">
                <span id="temp">--</span>
            </div>
        </div>

        <div class="stat shadow-lg border-t-4 border-blue-300">
            <div class="stat-title text-gray-600">Humidity:</div>
            <div class="stat-value text-blue-500">
                <span id="humi">--</span>
            </div>
        </div>

        <div class="stat shadow-lg border-t-4 border-yellow-400">
            <div class="stat-title text-gray-600">Light:</div>
            <div class="stat-value text-yellow-500">
                <span id="light">--</span>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="grid grid-cols-2 gap-4">
        <!-- LED -->
        <div class="stat shadow-xl border-l-4 border-green-500 p-4">
            <div class="stat-title text-gray-700 font-semibold">LED</div>
            <div class="text-sm text-gray-600 italic mb-2">
                Mode: <span class="font-bold">{{ $ledAuto ? 'Auto' : 'Manual' }}</span>
            </div>
            <div class="flex gap-3">
                <button
                    class="flex-1 px-4 py-2 rounded-lg font-semibold text-white transition {{ $ledAuto ? 'bg-gray-300 cursor-not-allowed' : ($led ? 'bg-red-500 hover:bg-red-600 cursor-pointer' : 'bg-green-500 hover:bg-green-600 cursor-pointer') }}"
                    wire:click="toggleLed()" @if ($ledAuto) disabled @endif>
                    {{ $led ? 'Off' : 'On' }}
                </button>
                <button
                    class="flex-1 px-4 py-2 rounded-lg font-semibold transition cursor-pointer {{ $ledAuto ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-400 hover:bg-gray-500 text-white' }}"
                    wire:click="setLedAuto()">
                    {{ $ledAuto ? 'Cancel' : 'Auto' }}
                </button>
            </div>
        </div>

        <!-- Pump -->
        <div class="stat shadow-xl border-l-4 border-blue-500 p-4">
            <div class="stat-title text-gray-700 font-semibold">Pump</div>
            <div class="text-sm text-gray-600 italic mb-2">
                Mode: <span class="font-bold">{{ $pumpAuto ? 'Auto' : 'Manual' }}</span>
            </div>
            <div class="flex gap-3">
                <button
                    class="flex-1 px-4 py-2 rounded-lg font-semibold text-white transition {{ $pumpAuto ? 'bg-gray-300 cursor-not-allowed' : ($pump ? 'bg-red-500 hover:bg-red-600 cursor-pointer' : 'bg-green-500 hover:bg-green-600 cursor-pointer') }}"
                    wire:click="togglePump" @if ($pumpAuto) disabled @endif>
                    {{ $pump ? 'Off' : 'On' }}
                </button>
                <button
                    class="flex-1 px-4 py-2 rounded-lg font-semibold transition cursor-pointer {{ $pumpAuto ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-400 hover:bg-gray-500 text-white' }}"
                    wire:click="setPumpAuto">
                    {{ $pumpAuto ? 'Cancel' : 'Auto' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="my-4">
        <canvas id="myChart"></canvas>
    </div>

    <!-- Alert Popup Container -->
    <div id="alert-container" x-data="alertHandler()" x-init class="fixed bottom-4 right-4 z-50 space-y-2">
        <template x-for="alert in alerts" :key="alert.id">
            <div x-show="true" x-transition class="bg-white border-l-4 shadow-lg p-4 w-72"
                :class="alert.type === 'warning' ? 'border-red-500' : 'border-blue-500'">
                <div class="text-sm font-semibold mb-1"
                    :class="alert.type === 'warning' ? 'text-red-600' : 'text-blue-600'">
                    <template x-if="alert.type === 'warning'">Warning</template>
                    <template x-if="alert.type === 'info'">Info</template>
                </div>
                <div class="text-gray-700 text-sm" x-text="alert.msg"></div>
            </div>
        </template>
    </div>
</div>

<!-- Alert -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('alertHandler', () => ({
            alerts: [],
            addAlert(msg, type) {
                const id = Date.now() + Math.random();
                this.alerts.push({
                    id,
                    msg,
                    type
                });
                setTimeout(() => {
                    this.alerts = this.alerts.filter(alert => alert.id !== id);
                }, 4000);
            }
        }))
    });
</script>

<!-- MQTT Connection -->
<script>
    const clientId = 'mqttjs_' + Math.random().toString(16).substr(2, 8);
    const host = 'ws://broker.emqx.io:8083/mqtt';
    const options = {
        keepalive: 60,
        clientId: clientId,
        protocolId: 'MQTT',
        protocolVersion: 4,
        clean: true,
        reconnectPeriod: 1000,
        connectTimeout: 60 * 1000,
        will: {
            topic: 'WillMsg',
            payload: 'Connection Closed abnormally..!',
            qos: 0,
            retain: false
        },
    };
    const client = mqtt.connect(host, options);

    client.on('connect', () => {
        console.log(`MQTT Connected: ${clientId}`);
        client.subscribe('asm_iot_quanh', {
            qos: 0
        });
    });

    client.on('error', (err) => {
        console.error('MQTT Connection error: ', err);
        client.end();
    });

    client.on('reconnect', () => {
        console.log('Reconnecting MQTT...');
    });
</script>

<!-- Chart-->
<script>
    let count = 0;
    let labels = [];
    let tempData = [];
    let humiData = [];

    const ctx = document.getElementById('myChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                    label: 'Temperature (°C)',
                    data: tempData,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 2
                },
                {
                    label: 'Humidity (%)',
                    data: humiData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
</script>

<!-- MQTT Message -->
<script>
    client.on('message', (topic, message) => {
        try {
            const payload = JSON.parse(message.toString());

            const temp = payload.temp ?? 0;
            const humi = payload.humi ?? 0;
            const light = payload.light ?? 0;

            document.getElementById('temp').innerHTML = temp + ' °C';
            document.getElementById('humi').innerHTML = humi + ' %';
            document.getElementById('light').innerHTML = light;

            count++;
            labels.push(count);
            tempData.push(temp);
            humiData.push(humi);

            if (labels.length > 20) {
                labels.shift();
                tempData.shift();
                humiData.shift();
            }

            chart.update();

            const popupScope = Alpine.$data(document.getElementById('alert-container'));
            if (temp > 40) popupScope.addAlert(`Current temperature is ${temp}°C. Please check your system.`,
                'warning');
            if (humi < 30) popupScope.addAlert(`Current humidity is ${humi}%. Consider watering.`, 'info');

            fetch('/sensor-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        temp,
                        humi,
                        light
                    })
                })
                .then(res => res.json())
                .then(response => {
                    console.log("Saved to DB:", response);
                })
                .catch(err => {
                    console.error("Error sending data to Laravel:", err);
                });
        } catch (err) {
            console.error("MQTT message parse error:", err);
        }
    });
</script>
