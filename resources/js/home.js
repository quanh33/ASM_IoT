const clientId = "mqttjs_" + Math.random().toString(16).substr(2, 8);
const host = "ws://broker.emqx.io:8083/mqtt";
const options = {
    keepalive: 60,
    clientId: clientId,
    protocolId: "MQTT",
    protocolVersion: 4,
    clean: true,
    reconnectPeriod: 1000,
    connectTimeout: 30 * 1000,
    will: {
        topic: "WillMsg",
        payload: "Connection Closed abnormally..!",
        qos: 0,
        retain: false,
    },
};
console.log("Connecting mqtt client");
const client = mqtt.connect(host, options);
client.on("error", (err) => {
    console.log("Connection error: ", err);
    client.end();
});
client.on("reconnect", () => {
    console.log("Reconnecting...");
});

client.on("connect", () => {
    console.log(`Client connected: ${clientId}`);
    // Subscribe
    client.subscribe("asm_iot_quanh", {
        qos: 0,
    });
});

client.on("message", (topic, message) => {
    try {
        const data = JSON.parse(message.toString());

        if (data.temp !== undefined) {
            document.getElementById("temp").innerHTML = data.temp + " °C";
        }
        if (data.humi !== undefined) {
            document.getElementById("humi").innerHTML = data.humi + " %";
        }
        if (data.light !== undefined) {
            document.getElementById("light").innerHTML = data.light;
        }

        console.log("Data receive:", data);
    } catch (e) {
        console.error("JSON error: ", message.toString(), e);
    }
});
