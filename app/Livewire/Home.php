<?php

namespace App\Livewire;

use Livewire\Component;
use PhpMqtt\Client\Facades\MQTT;

class Home extends Component
{
    public bool $led = false;
    public bool $ledAuto = true;

    public function toggleLed()
    {
        if ($this->ledAuto) return;

        $this->led = !$this->led;
        MQTT::publish('asm_iot_quanh', $this->led ? "on" : "off");
    }

    public function setLedAuto()
    {
        $this->ledAuto = !$this->ledAuto;

        if ($this->ledAuto) {
            MQTT::publish('asm_iot_quanh', 'auto');
        } else {
            $this->led = false;
            MQTT::publish('asm_iot_quanh', 'off');
        }
    }


    public bool $pump = false;
    public bool $pumpAuto = true;

    public function togglePump()
    {
        if ($this->pumpAuto) return;

        $this->pump = !$this->pump;
        MQTT::publish('asm_iot_quanh', $this->pump ? 'pump_on' : 'pump_off');
    }

    public function setPumpAuto()
    {
        $this->pumpAuto = !$this->pumpAuto;

        if ($this->pumpAuto) {
            MQTT::publish('asm_iot_quanh', 'pump_auto');
        } else {
            $this->pump = false;
            MQTT::publish('asm_iot_quanh', 'pump_off');
        }
    }

    public function render()
    {
        return view('livewire.home');
    }
}
