<?php

namespace App\Providers;

use App\Models\ReportedUser;
use App\Nova\Dashboards\SaasDashboard;
use Illuminate\Support\ServiceProvider;

class NovaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! class_exists(\Laravel\Nova\Nova::class)) {
            return;
        }

        \Laravel\Nova\Nova::resourcesIn(app_path('Nova/Resources'));
        \Laravel\Nova\Nova::dashboards([
            new SaasDashboard,
        ]);

        $this->gate();
        $this->menu();
    }

    private function gate(): void
    {
        if (! class_exists(\Laravel\Nova\Nova::class)) {
            return;
        }

        \Laravel\Nova\Nova::auth(function ($request) {
            return $request->user()?->is_admin === true;
        });
    }

    private function menu(): void
    {
        if (! class_exists(\Laravel\Nova\Menu\MenuSection::class)) {
            return;
        }

        \Laravel\Nova\Nova::mainMenu(function () {
            $openReports = ReportedUser::query()->where('status', 'open')->count();
            $reportedUsersLabel = $openReports > 0
                ? "Reported Users ({$openReports})"
                : 'Reported Users';

            return [
                \Laravel\Nova\Menu\MenuSection::dashboard(SaasDashboard::class)
                    ->icon('chart-bar'),
                \Laravel\Nova\Menu\MenuSection::resource(\App\Nova\Resources\User::class)
                    ->icon('users'),
                \Laravel\Nova\Menu\MenuSection::resource(\App\Nova\Resources\Game::class)
                    ->icon('rectangle-stack'),
                \Laravel\Nova\Menu\MenuSection::make($reportedUsersLabel, [
                    \Laravel\Nova\Menu\MenuItem::resource(\App\Nova\Resources\ReportedUser::class),
                ])->icon('bell-alert'),
                \Laravel\Nova\Menu\MenuSection::resource(\App\Nova\Resources\SaasMetricInput::class)
                    ->icon('currency-pound'),
            ];
        });
    }
}
