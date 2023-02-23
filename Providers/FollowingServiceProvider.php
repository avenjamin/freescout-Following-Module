<?php

namespace Modules\Following\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

define('FOLLOWING_MODULE', 'following');

class FollowingServiceProvider extends ServiceProvider
{
    const TYPE_FOLLOWING = 26;
    
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        
        // Following Folder Name
        \Eventy::addFilter('folder.type_name', function($name, $folder) {
            return ($folder->type == self::TYPE_FOLLOWING ? __('Following') : $name);
        }, 20, 2);


        // Following Folder Icon
        \Eventy::addFilter('folder.type_icon', function($icon, $folder) {
            return ($folder->type == self::TYPE_FOLLOWING ? 'bell' : $icon);
        }, 20, 2);


        // Following Folder sorting
        \Eventy::addFilter('folder.conversations_order_by', function($order_by, $folder_type) {
            return ($folder_type == self::TYPE_FOLLOWING ? [['updated_at' => 'desc']] : $order_by);
        }, 20, 2);
        
        
        // Following conversations query
        \Eventy::addFilter('folder.conversations_query', function($query_conversations, $folder, $user_id) {
            if ($folder->type == self::TYPE_FOLLOWING) {
                $followed_conversation_ids = self::getUserFollowingConversationIds($folder->mailbox_id, $user_id);
                $query_conversations = \App\Conversation::whereIn('id', $followed_conversation_ids);
            }
            return $query_conversations;
        }, 20, 3);
        
        
        // Following folder counter
        \Eventy::addFilter('folder.update_counters', function($update, $folder) {
            if ($folder->type == self::TYPE_FOLLOWING) {
                $folder->active_count = \App\Follower::where('followers.user_id', $folder->user_id)
                    ->join('conversations', 'conversations.id', '=', 'followers.conversation_id')
                    ->where('status', \App\Conversation::STATUS_ACTIVE)
                    ->count();
                $folder->total_count = \App\Follower::where('user_id', $folder->user_id)->count();
                $folder->save();
                $update = true;
            }
            return $update;
        }, 20, 2);
     
        
    }

    public static function getUserFollowingConversationIds($mailbox_id, $user_id = null)
    {
        // return \Cache::rememberForever('user_following_conversations_'.$user_id.'_'.$mailbox_id, function () use ($mailbox_id, $user_id) {
            // Get user's folder
            $folder = \App\Folder::select('id')
                        ->where('mailbox_id', $mailbox_id)
                        ->where('user_id', $user_id)
                        ->where('type', self::TYPE_FOLLOWING)
                        ->first();

            if ($folder) {
                return Follower::where('user_id', $user_id)
                    ->pluck('conversation_id')
                    ->toArray();
                
            } else {
                activity()
                    ->withProperties([
                        'error'    => "Folder not found (mailbox_id: $mailbox_id, user_id: $user_id)",
                     ])
                    ->useLog(\App\ActivityLog::NAME_SYSTEM)
                    ->log(\App\ActivityLog::DESCRIPTION_SYSTEM_ERROR);

                return [];
            }
        // });
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('following.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'following'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/following');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/following';
        }, \Config::get('view.paths')), [$sourcePath]), 'following');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
