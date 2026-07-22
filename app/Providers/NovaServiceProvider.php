<?php

namespace App\Providers;

use App\Models\ReportedUser;
use App\Nova\Dashboards\SaasDashboard;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        if (! class_exists(\Laravel\Nova\Nova::class)) {
            return;
        }

        $this->menu();
    }

    protected function authorization(): void
    {
        if (! class_exists(\Laravel\Nova\Nova::class)) {
            return;
        }

        Nova::auth(function ($request) {
            return $request->user()?->is_admin === true;
        });
    }

    protected function dashboards(): array
    {
        return [
            new SaasDashboard,
        ];
    }

    protected function resources(): void
    {
        Nova::resourcesIn(app_path('Nova/Resources'));
    }

    private function menu(): void
    {
        if (! class_exists(\Laravel\Nova\Menu\MenuSection::class)) {
            return;
        }

        Nova::mainMenu(function () {
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
