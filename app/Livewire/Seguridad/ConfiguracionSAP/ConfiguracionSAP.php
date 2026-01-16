<?php

namespace App\Livewire\Seguridad\ConfiguracionSAP;

use App\Models\Seguridad\ConfiguracionSAP as ConfiguracionSAPModel;
use Livewire\Component;
use Masmerise\Toaster\PendingToast;
use Illuminate\Database\QueryException;
use Exception;

class ConfiguracionSAP extends Component
{
    public $configId;
    public $base_url, $company_db, $username, $password, $route_id;
    public $cambios = false;

    protected $rules = [
        'base_url' => 'required|url',
        'company_db' => 'required|string',
        'username' => 'required|string',
        'password' => 'required|string',
        'route_id' => 'required|string',
    ];

    public function mount()
    {
        $config = ConfiguracionSAPModel::first();

        if ($config) {
            $this->configId = $config->id;
            $this->base_url = $config->base_url;
            $this->company_db = $config->company_db;
            $this->username = $config->username;
            $this->password = $config->password;
            $this->route_id = $config->route_id;
        }
    }

    public function updated($propertyName)
    {
        $this->cambios = true;
    }

    public function guardar()
    {
        try {
            $this->validate();

            if ($this->configId) {
                $config = ConfiguracionSAPModel::find($this->configId);
                $config->update([
                    'base_url' => $this->base_url,
                    'company_db' => $this->company_db,
                    'username' => $this->username,
                    'password' => $this->password,
                    'route_id' => $this->route_id,
                ]);
            } else {
                $config = ConfiguracionSAPModel::create([
                    'base_url' => $this->base_url,
                    'company_db' => $this->company_db,
                    'username' => $this->username,
                    'password' => $this->password,
                    'route_id' => $this->route_id,
                ]);
                $this->configId = $config->id;
            }

            PendingToast::create()
                ->message('Configuración actualizada correctamente')
                ->duration(6000)
                ->success();

            $this->cambios = false;

        } catch (QueryException $e) {
            PendingToast::create()
                ->message('Error de base de datos: ' . $e->getMessage())
                ->duration(8000)
                ->error();
        } catch (Exception $e) {
            PendingToast::create()
                ->message('Error inesperado: ' . $e->getMessage())
                ->duration(8000)
                ->error();
        }
    }

    public function render()
    {
        return view('livewire.seguridad.configuracion-s-a-p.configuracion-s-a-p');
    }
}
