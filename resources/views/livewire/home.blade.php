<script>
    const clientId = 'mqttjs_' + Math.random().toString(16).substr(2, 8)
    const host = 'ws://broker.emqx.io:8083/mqtt'
    const options = {
        keepalive: 60,
        clientId: clientId,
        protocolId: 'MQTT',
        protocolVersion: 4,
        clean: true,
        reconnectPeriod: 1000,
        connectTimeout: 30 * 1000,
        will: {
            topic: 'WillMsg',
            payload: 'Connection Closed abnormally..!',
            qos: 0,
            retain: false
        },
    }
    console.log('Connecting mqtt client')
    const client = mqtt.connect(host, options)
    client.on('error', (err) => {
        console.log('Connection error: ', err)
        client.end()
    })
    client.on('reconnect', () => {
        console.log('Reconnecting...')
    })

    client.on('connect', () => {
        console.log(`Client connected: ${clientId}`)
        // Subscribe
        client.subscribe('asm_iot_quanh', {
            qos: 0
        })
    })

    client.on('message', (topic, message) => {
        try {
            const data = JSON.parse(message.toString());

            if (data.temp !== undefined) {
                document.getElementById('temp').innerHTML = data.temp + ' °C';
            }
            if (data.humi !== undefined) {
                document.getElementById('humi').innerHTML = data.humi + ' %';
            }
            if (data.light !== undefined) {
                document.getElementById('light').innerHTML = data.light;
            }

            console.log("Data receive:", data);
        } catch (e) {
            console.error("JSON error: ", message.toString(), e);
        }
    });
</script>

<div class="p-4 space-y-4">
    <h1 class="text-2xl font-bold text-primary">🌱 Smart IoT App</h1>
    <hr class="border-gray-300" />

    <!-- Sensor stats -->
    <div wire:poll.1000ms class="grid grid-cols-3 md:grid-cols-3 gap-4">
        <div class="stat shadow-lg border-t-4 border-orange-400">
            <div class="stat-title text-gray-600">Nhiệt độ:</div>
            <div class="stat-value text-orange-500">
                <span id="temp">--</span>
            </div>
        </div>

        <div class="stat shadow-lg border-t-4 border-blue-300">
            <div class="stat-title text-gray-600">Độ ẩm:</div>
            <div class="stat-value text-blue-500">
                <span id="humi">--</span>
            </div>
        </div>

        <div class="stat shadow-lg border-t-4 border-yellow-400">
            <div class="stat-title text-gray-600">Ánh sáng:</div>
            <div class="stat-value text-yellow-500">
                <span id="light">--</span>
            </div>
        </div>
    </div>


    <!--Controls -->
    <div class="grid grid-cols-2 gap-4">
        <!-- LED-->
        <div class="stat shadow-xl border-l-4 border-green-500 p-4">
            <div class="stat-title text-gray-700 font-semibold">Đèn trang trại</div>
            <div class="text-sm text-gray-600 italic mb-2">
                Chế độ: <span class="font-bold">{{ $ledAuto ? 'Tự động' : 'Thủ công' }}</span>
            </div>

            <div class="flex gap-3">
                <button
                    class="flex-1 px-4 py-2 rounded-lg font-semibold text-white transition
                        {{ $ledAuto ? 'bg-gray-300 cursor-not-allowed' : ($led ? 'bg-red-500 hover:bg-red-600 cursor-pointer' : 'bg-green-500 hover:bg-green-600 cursor-pointer') }}"
                    wire:click="toggleLed()" @if ($ledAuto) disabled @endif>
                    {{ $led ? 'Tắt' : 'Bật' }}
                </button>
                <button
                    class="flex-1 px-4 py-2 rounded-lg font-semibold transition cursor-pointer
                        {{ $ledAuto ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-400 hover:bg-gray-500 text-white' }}"
                    wire:click="setLedAuto()">
                    {{ $ledAuto ? 'Đang tự động' : 'Chuyển sang Auto' }}
                </button>

                <div class="cursor-pointer" wire:click="led_auto()">
                    Haha
                </div>
            </div>
        </div>

        <!-- Máy bơm -->
        <div class="stat shadow-xl border-l-4 border-blue-500 p-4">
            <div class="stat-title text-gray-700 font-semibold">Máy bơm</div>
            <div class="text-sm text-gray-600 italic mb-2">
                Chế độ: <span class="font-bold">{{ $pumpAuto ? 'Tự động' : 'Thủ công' }}</span>
            </div>

            <div class="flex gap-3">
                <button
                    class="flex-1 px-4 py-2 rounded-lg font-semibold text-white transition
                {{ $pumpAuto ? 'bg-gray-300 cursor-not-allowed' : ($pump ? 'bg-red-500 hover:bg-red-600 cursor-pointer' : 'bg-green-500 hover:bg-green-600 cursor-pointer') }}"
                    wire:click="togglePump" @if ($pumpAuto) disabled @endif>
                    {{ $pump ? 'Tắt' : 'Bật' }}
                </button>

                <button
                    class="flex-1 px-4 py-2 rounded-lg font-semibold transition cursor-pointer
                {{ $pumpAuto ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-400 hover:bg-gray-500 text-white' }}"
                    wire:click="setPumpAuto">
                    {{ $pumpAuto ? 'Đang tự động' : 'Chuyển sang Auto' }}
                </button>
            </div>
        </div>
    </div>
</div>
