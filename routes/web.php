<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataFeedController;
use App\Http\Controllers\DashboardController;



use App\Http\Controllers\MemberController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\JobController;

use App\Http\Controllers\Usuarios\UsuariosController;
use App\Livewire\Bodegas\Bodega;
use App\Livewire\Bodegas\Edit;
use App\Livewire\Categoria\Categorias;
use App\Livewire\Categorias\IndexCategorias;
use App\Livewire\IntegracionSAP\LoginSAP;
use App\Livewire\IntegracionSAP\PurchaseOrdersSAP;
use App\Livewire\Inventario\EntradasMercancia;
use App\Livewire\OperacionesStock\OperacionesStock;
use App\Livewire\Productos\Productos;
use App\Livewire\Proveedores\EvaluacionProveedores;
use App\Livewire\Seguridad\ConfiguracionSAP\ConfiguracionSAP;
use App\Livewire\Usuarios\Usuarios;
use App\Livewire\Seguridad\Roles\IndexP as IndexP;
use App\Livewire\Seguridad\Roles\Index as RolesIndex;
use App\Livewire\Seguridad\Roles\Create as RolesCreate;
use App\Livewire\Seguridad\Roles\Edit as RolesEdit;
use App\Livewire\SocioNegocio\SocioNegocios;
use App\Livewire\SubCategorias\SubCategorias;
use App\Livewire\Usuarios\CambiarContrasenaUsuario;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', 'login');
// Route::redirect('/register', '/login');
Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    // Route for the getting the data feed
    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/dashboard/fintech', [DashboardController::class, 'fintech'])->name('fintech');



  
   
    Route::get('/community/profile', function () {
        return view('pages/community/profile');
    })->name('profile');
    Route::get('/community/feed', function () {
        return view('pages/community/feed'); 
    })->name('feed');     
    Route::get('/community/forum', function () {
        return view('pages/community/forum');
    })->name('forum');
    Route::get('/community/forum-post', function () {
        return view('pages/community/forum-post');
    })->name('forum-post');    
    Route::get('/community/meetups', function () {
        return view('pages/community/meetups');
    })->name('meetups');    
    Route::get('/community/meetups-post', function () {
        return view('pages/community/meetups-post');
    })->name('meetups-post');    
    Route::get('/finance/cards', function () {
        return view('pages/finance/credit-cards');
    })->name('credit-cards');
    Route::get('/job/job-post', function () {
        return view('pages/job/job-post');
    })->name('job-post');    
    Route::get('/job/company-profile', function () {
        return view('pages/job/company-profile');
    })->name('company-profile');
    Route::get('/messages', function () {
        return view('pages/messages');
    })->name('messages');
    Route::get('/tasks/kanban', function () {
        return view('pages/tasks/tasks-kanban');
    })->name('tasks-kanban');
    Route::get('/tasks/list', function () {
        return view('pages/tasks/tasks-list');
    })->name('tasks-list');       
    Route::get('/inbox', function () {
        return view('pages/inbox');
    })->name('inbox'); 
    Route::get('/calendar', function () {
        return view('pages/calendar');
    })->name('calendar'); 
    Route::get('/settings/account', function () {
        return view('pages/settings/account');
    })->name('account');  
    Route::get('/settings/notifications', function () {
        return view('pages/settings/notifications');
    })->name('notifications');  
    Route::get('/settings/apps', function () {
        return view('pages/settings/apps');
    })->name('apps');
    Route::get('/settings/plans', function () {
        return view('pages/settings/plans');
    })->name('plans');      
    Route::get('/settings/billing', function () {
        return view('pages/settings/billing');
    })->name('billing');  
    Route::get('/settings/feedback', function () {
        return view('pages/settings/feedback');
    })->name('feedback');
    Route::get('/utility/changelog', function () {
        return view('pages/utility/changelog');
    })->name('changelog');  
    Route::get('/utility/roadmap', function () {
        return view('pages/utility/roadmap');
    })->name('roadmap');  
    Route::get('/utility/faqs', function () {
        return view('pages/utility/faqs');
    })->name('faqs');  
    Route::get('/utility/empty-state', function () {
        return view('pages/utility/empty-state');
    })->name('empty-state');  
    Route::get('/utility/404', function () {
        return view('pages/utility/404');
    })->name('404');
    Route::get('/utility/knowledge-base', function () {
        return view('pages/utility/knowledge-base');
    })->name('knowledge-base');
    Route::get('/onboarding-01', function () {
        return view('pages/onboarding-01');
    })->name('onboarding-01');   
    Route::get('/onboarding-02', function () {
        return view('pages/onboarding-02');
    })->name('onboarding-02');   
    Route::get('/onboarding-03', function () {
        return view('pages/onboarding-03');
    })->name('onboarding-03');   
    Route::get('/onboarding-04', function () {
        return view('pages/onboarding-04');
    })->name('onboarding-04');
    Route::get('/component/button', function () {
        return view('pages/component/button-page');
    })->name('button-page');
    Route::get('/component/form', function () {
        return view('pages/component/form-page');
    })->name('form-page');
    Route::get('/component/dropdown', function () {
        return view('pages/component/dropdown-page');
    })->name('dropdown-page');
    Route::get('/component/alert', function () {
        return view('pages/component/alert-page');
    })->name('alert-page');
    Route::get('/component/modal', function () {
        return view('pages/component/modal-page');
    })->name('modal-page'); 
    Route::get('/component/pagination', function () {
        return view('pages/component/pagination-page');
    })->name('pagination-page');
    Route::get('/component/tabs', function () {
        return view('pages/component/tabs-page');
    })->name('tabs-page');
    Route::get('/component/breadcrumb', function () {
        return view('pages/component/breadcrumb-page');
    })->name('breadcrumb-page');
    Route::get('/component/badge', function () {
        return view('pages/component/badge-page');
    })->name('badge-page'); 
    Route::get('/component/avatar', function () {
        return view('pages/component/avatar-page');
    })->name('avatar-page');
    Route::get('/component/tooltip', function () {
        return view('pages/component/tooltip-page');
    })->name('tooltip-page');
    Route::get('/component/accordion', function () {
        return view('pages/component/accordion-page');
    })->name('accordion-page');
    Route::get('/component/icons', function () {
        return view('pages/component/icons-page');
    })->name('icons-page');
    Route::fallback(function() {
        return view('pages/utility/404');
    });    



    Route::get('/' . config('app.name') . '/Usuarios', Usuarios::class)->name('Usuarios');
    Route::middleware(['auth'])->group(function () {
        
        Route::get('/roles', IndexP::class)->name('roles.index');
        Route::get('/permisos', RolesIndex::class)->name('roles.index2');
        Route::get('/roles/crear', RolesCreate::class)->name('roles.create');
        Route::get('/roles/editar/{id}', RolesEdit::class)->name('roles.edit');
    });

  
    //usuarios
    Route::put('/usuarios/cambiar-contrasena/{usuarioId}', [Usuarios::class, 'updatePassword'])->name('usuarios.actualizar-contrasena');
    //Evaluacion de proveedores
    Route::get('/Evaluacion-Proveedores', EvaluacionProveedores::class)->name('Evaluacion-Proveedores');
    Route::get('/ConfiguracionSAP', ConfiguracionSAP::class)->name('sap.ConfiguracionSAP');

});
