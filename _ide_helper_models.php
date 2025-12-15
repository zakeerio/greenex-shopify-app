<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $shopify_grandfathered
 * @property string|null $shopify_namespace
 * @property int $shopify_freemium
 * @property int|null $plan_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $password_updated_at
 * @property int|null $theme_support_level
 * @property string|null $shopify_domain
 * @property string|null $access_token
 * @property string|null $shop_domain
 * @property string|null $shopify_access_token
 * @property string|null $shopify_api_version
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Osiset\ShopifyApp\Storage\Models\Charge> $charges
 * @property-read int|null $charges_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Osiset\ShopifyApp\Storage\Models\Plan|null $plan
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePasswordUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShopDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShopifyAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShopifyApiVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShopifyDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShopifyFreemium($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShopifyGrandfathered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShopifyNamespace($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereThemeSupportLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 */
	class User extends \Eloquent implements \Osiset\ShopifyApp\Contracts\ShopModel {}
}

