<?php

namespace App\Providers;

use App\Interfaces\BooksRepositoryInterface;
use App\Interfaces\CandidateDetailsInterface;
use App\Interfaces\CandidateInterviewInterface;
use App\Interfaces\ClientsRepositoryInterface;
use App\Repositories\RolesRepository;
use App\Repositories\UsersRepository;
use App\Repositories\LeavesRepository;
use App\Repositories\HolidayRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\PoliciesRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\DesignationRepository;
use App\Repositories\JobOpeningsRepository;
use App\Interfaces\RolesRepositoryInterface;
use App\Interfaces\UsersRepositoryInterface;
use App\Repositories\WorkFromHomeRepository;
use App\Interfaces\LeavesRepositoryInterface;
use App\Repositories\MonthlyEventsRepository;
use App\Interfaces\HolidayRepositoryInterface;
use App\Interfaces\PoliciesRepositoryInterface;
use App\Interfaces\DepartmentRepositoryInterface;
use App\Interfaces\DesignationRepositoryInterface;
use App\Interfaces\FreelancerInterface;
use App\Interfaces\InviteUsersRepositoryInterface;
use App\Interfaces\JobOpeningsRepositoryInterface;
use App\Interfaces\WorkFromHomeRepositoryInterface;
use App\Interfaces\MonthlyEventsRepositoryInterface;
use App\Interfaces\PunchLogsRepositoryInterface;
use App\Interfaces\TechnologyRepositoryInterface;
use App\Repositories\BooksRepository;
use App\Interfaces\MeetingsRepositoryInterface;
use App\Interfaces\ProjectAssigneeInterface;
use App\Interfaces\ProjectInterface;
use App\Interfaces\ProjectTaskInterface;
use App\Interfaces\ResourceRequestInterface;
use App\Repositories\CandidateDetailsRepository;
use App\Repositories\CandidateInterviewRepository;
use App\Repositories\ClientsRepository;
use App\Repositories\FreelancerRepository;
use App\Repositories\InviteUserRepository;
use App\Repositories\PunchLogsRepository;
use App\Repositories\TechnologyRepository;
use App\Repositories\MeetingsRepository;
use App\Repositories\ProjectAssigneeRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\ProjectTaskRepository;
use App\Repositories\ResourceRequestRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            DepartmentRepositoryInterface::class,
            DepartmentRepository::class
        );
        $this->app->bind(
            JobOpeningsRepositoryInterface::class,
            JobOpeningsRepository::class
        );
        $this->app->bind(
            DesignationRepositoryInterface::class,
            DesignationRepository::class
        );
        $this->app->bind(
            MonthlyEventsRepositoryInterface::class,
            MonthlyEventsRepository::class
        );
        $this->app->bind(
            HolidayRepositoryInterface::class,
            HolidayRepository::class
        );
        $this->app->bind(
            PoliciesRepositoryInterface::class,
            PoliciesRepository::class
        );
        $this->app->bind(
            LeavesRepositoryInterface::class,
            LeavesRepository::class
        );
        $this->app->bind(
            WorkFromHomeRepositoryInterface::class,
            WorkFromHomeRepository::class
        );
        $this->app->bind(
            RolesRepositoryInterface::class,
            RolesRepository::class
        );
        $this->app->bind(
            UsersRepositoryInterface::class,
            UsersRepository::class
        );
        $this->app->bind(
            InviteUsersRepositoryInterface::class,
            InviteUserRepository::class
        );
        $this->app->bind(
            TechnologyRepositoryInterface::class,
            TechnologyRepository::class
        );
        $this->app->bind(
            PunchLogsRepositoryInterface::class,
            PunchLogsRepository::class
        );
        $this->app->bind(
            ClientsRepositoryInterface::class,
            ClientsRepository::class
        );
        $this->app->bind(
            BooksRepositoryInterface::class,
            BooksRepository::class
        );
        $this->app->bind(
            MeetingsRepositoryInterface::class,
            MeetingsRepository::class
        );
        $this->app->bind(
            ResourceRequestInterface::class,
            ResourceRequestRepository::class
        );
        $this->app->bind(
            ProjectInterface::class,
            ProjectRepository::class
        );
        $this->app->bind(
            ProjectAssigneeInterface::class,
            ProjectAssigneeRepository::class
        );
        $this->app->bind(
            ProjectTaskInterface::class,
            ProjectTaskRepository::class
        );
        $this->app->bind(
            FreelancerInterface::class,
            FreelancerRepository::class
        );
        $this->app->bind(
            CandidateDetailsInterface::class,
            CandidateDetailsRepository::class
        );
        $this->app->bind(
            CandidateInterviewInterface::class,
            CandidateInterviewRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}