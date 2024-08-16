<?php


use App\Http\Controllers\AccessoryCategoryController;
use App\Http\Controllers\AccessoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppRatingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DrinkCategoryController;
use App\Http\Controllers\DrinkController;
use App\Http\Controllers\EventSupplementController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FcmController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FoodCategoryController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\HostDrinkCategoryController;
use App\Http\Controllers\HostFoodCategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MainEventController;
use App\Http\Controllers\MainEventHostController;
use App\Http\Controllers\MEHACController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TestsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEventController;
use App\Http\Controllers\UserJoinedEventController;
use App\Http\Controllers\WalletChargeController;
use App\Http\Controllers\WarehouseController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\OwnerMiddleware;
use App\Http\Middleware\UserMiddleware;
use App\Models\AppRating;
use App\Models\User;
use App\Models\UserJoinedEvent;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


/*...................................<Mahmoud>...................................*/

//register new user
Route::post('/register', [AuthController::class, 'register']);

//verify user email to continue to the app
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

//login for user
Route::post('/login', [AuthController::class, 'login']);

//login for admin
Route::post('/login/admin', [AuthController::class, 'login']);



//requesting code through email to change password
Route::post('/password/email',  [AuthController::class , 'forgotPassword']);

//checking code validity
Route::post('/password/code/check', [AuthController::class , 'checkCode']);

//resetting user password
Route::post('/password/reset', [AuthController::class , 'resetPassword']);



//redirecting to google for authentication for mobile
Route::get('/mobile/auth/google', [SocialController::class, 'redirectToGoogleAPIMobile']);

//handling google callback with user info for mobile
Route::get('/mobile/auth/google/callback' ,[SocialController::class, 'handleGoogleAPICallbackMobile']);

//redirecting to google for authentication for dashboard
Route::get('/web/auth/google', [SocialController::class, 'redirectToGoogleAPIWeb']);

//handling google callback with user info for dashboard
Route::get('/web/auth/google/callback' ,[SocialController::class, 'handleGoogleAPICallbackWeb']);


Route::post('/update-device-token', [FcmController::class, 'updateDeviceToken']);

Route::post('/send-fcm-notification', [FcmController::class, 'sendFcmNotification']);


Route::middleware([UserMiddleware::class])->group(function () {

    //logout user
    Route::post('/logout', [AuthController::class, 'logout']);



    Route::prefix("user")->group(function (){

        Route::prefix("profile")->group(function (){

            //getting user general info
            Route::get('/general', [ProfileController::class, 'getGeneralInfo']);

            //updating user general info
            Route::post('/general', [ProfileController::class , 'updateProfileGeneralInfo']);

            //updating user profile picture
            Route::post('/picture', [ProfileController::class , 'updateProfilePicture']);

            //getting user privacy info
            Route::get('/privacy', [ProfileController::class, 'getPrivacyInfo']);

            //updating user privacy info
            Route::post('/privacy', [ProfileController::class , 'updateProfilePrivacyInfo']);

            //updating user preferred language
            Route::post('/language', [ProfileController::class , 'updatePreferredLanguage']);

            //updating user preferred currency
            Route::post('/currency', [ProfileController::class , 'updatePreferredCurrency']);

            //get user favorites
            Route::get('/favorites', [FavoriteController::class, 'getUserFavorites']);

            //add location to user favorites
            Route::post('/favorites', [FavoriteController::class , 'addToFavorites']);

            //remove location from user favorites
            Route::post('/favorites/remove', [FavoriteController::class , 'removeFromFavorites']);

            //delete user account
            Route::delete('/delete-account', [ProfileController::class , 'deleteAccount']);

        });


        Route::prefix("cart")->group(function (){

            //add item to user cart
            Route::post('/add-items', [CartController::class , 'addToCart']);


            //remove item from user cart
            Route::post('/remove-item', [CartController::class , 'removeFromCart']);


            //update item quantity in cart
            Route::post('/update-item', [CartController::class , 'updateCartQuantity']);


            //get the declined food and drinks for user
            Route::get('/declined_food&drinks',[EventSupplementController::class, 'getDeclinedFoodAndDrinks']);


            //get the declined accessories for user
            Route::get('/declined_accessories',[EventSupplementController::class, 'getDeclinedAccessories']);

        });

        Route::prefix("events")->group(function (){

            //create an event for user
            Route::post('/', [UserEventController::class , 'createEvent']);

            //get event details
            Route::get('/{event_id}/details', [UserEventController::class , 'getEventDetails']);


            //update event details
            Route::post('/details', [UserEventController::class , 'updateEventDetails']);


            //delete an event
            Route::delete('/{event_id}', [UserEventController::class , 'deleteEvent']);





            //get supplements for user event
            Route::get('/{event_id}/supplements', [EventSupplementController::class , 'getSupplementsForEvent']);


            //get food supplements for user event
            Route::get('/{event_id}/supplements/food', [EventSupplementController::class , 'getFoodSupplementsForEvent']);


            //get drinks supplements for user event
            Route::get('/{event_id}/supplements/drinks', [EventSupplementController::class , 'getDrinksSupplementsForEvent']);


            //get accessories supplements for user event
            Route::get('/{event_id}/supplements/accessories', [EventSupplementController::class , 'getAccessoriesSupplementsForEvent']);


            //add food and drinks to user's event supplements
            Route::post('/{event_id}/food&drinks', [EventSupplementController::class , 'processFoodAndDrinksSupplements']);

            //add accessories to user's event supplements
            Route::post('/{event_id}/accessories', [EventSupplementController::class , 'processAccessoriesSupplements']);

            //generate QR code for event receipt
            Route::get('/{event_id}/qr', [ReceiptController::class, 'generateQRForReceipt']);



            //add item to event supplements
            Route::post('/supplements/add', [EventSupplementController::class , 'addSupplement']);


            //update item quantity in event supplements
            Route::post('/supplements/update', [EventSupplementController::class , 'updateSupplement']);


            // remove item from event supplements
            Route::post('/supplements/remove', [EventSupplementController::class , 'removeSupplement']);


        });

        Route::prefix("statistics")->group(function (){

            //get statistics for registration, feedbacks and favorites
            Route::get('/digital', [StatisticsController::class, 'getDigitalStatisticsForUser']);

            //get food, drinks and accessories statistics for charts
            Route::get('/schemes', [StatisticsController::class, 'getPurchasesDistributions']);

            //get some user info counts
            Route::get('/counts', [StatisticsController::class, 'getUserStatisticsCount']);


        });

    });

    Route::prefix("locations")->group(function (){

        //explore all locations available
        Route::get('/', [LocationController::class, 'getLocations']);

        //find location by its id
        Route::get('/{location_id}', [LocationController::class, 'getLocationById']);
    });


    Route::prefix("food")->group(function (){

        //explore all available food categories
        Route::get('/categories', [FoodController::class, 'getFoodCategories']);

        //explore all food for specific category
        Route::get('/category/{category_id}', [FoodController::class, 'getFoodByCategory']);

        //find food by its id
        Route::get('/{food_id}', [FoodController::class, 'getFoodById']);
    });


    Route::prefix("drinks")->group(function (){

        //explore all available drinks categories
        Route::get('/categories', [DrinkController::class, 'getDrinksCategories']);

        //explore all drinks for specific category
        Route::get('/category/{category_id}', [DrinkController::class, 'getDrinksByCategory']);

        //find drink by its id
        Route::get('/{drink_id}', [DrinkController::class, 'getDrinkById']);
    });


    Route::prefix("accessories")->group(function (){

        //explore all available drinks categories
        Route::get('/categories', [AccessoryController::class, 'getAccessoriesCategories']);

        //explore all accessories for specific category
        Route::get('/category/{category_id}', [AccessoryController::class, 'getAccessoriesByCategory']);

        //find accessory by its id
        Route::get('/{accessory_id}', [AccessoryController::class, 'getAccessoryById']);
    });

    Route::prefix("notifications")->group(function (){

        //get number of unread notifications
        Route::get('/count', [NotificationController::class, 'countUnreadNotifications']);

        //mark notification as read
        Route::get('/mark-read/{notification_id}', [NotificationController::class, 'markNotificationAsRead']);

        //get notifications
        Route::get('/user', [NotificationController::class, 'getUserNotifications']);

    });

    Route::get('/user/wallet', [WalletChargeController::class, 'getUserBalanceWithHistory']);


});


Route::middleware([AdminMiddleware::class])->group(function () {

    Route::prefix("admin")->group(function (){

        Route::prefix("events/{event_id}")->group(function () {

            Route::prefix("supplements")->group(function () {

                //get food supplements details for a user event
                Route::get('/food', [EventSupplementController::class , 'getFoodSupplementsForSomeUserEvent']);

                //get drinks supplements details for a user event
                Route::get('/drinks', [EventSupplementController::class , 'getDrinksSupplementsForSomeUserEvent']);

                //get accessories supplements details for a user event
                Route::get('/accessories', [EventSupplementController::class , 'getAccessoriesSupplementsForSomeUserEvent']);

            });


            //accept user reservation
            Route::post('/accept', [AdminController::class , 'acceptReservation']);

            //reject user reservation
            Route::post('/reject', [AdminController::class , 'rejectReservation']);

        });

        //get weekly statistics about food, drinks and accessories for web charts
        Route::get('/weekly-statistics', [StatisticsController::class, 'getWeeklyStatistics']);

        //get admin notifications
        Route::get('/notifications', [NotificationController::class, 'getAdminNotifications']);

        //create a notification
        Route::post('/notifications', [NotificationController::class, 'create']);

    });

});

//get weekly report as PDF
Route::get('/admin/weekly-report', [StatisticsController::class, 'getWeeklyReport']);

Route::middleware([OwnerMiddleware::class])->group(function () {

    Route::prefix("owner")->group(function () {


        //delete user reservation
        Route::delete('events/{event_id}/delete', [OwnerController::class , 'deleteReservation']);


        Route::prefix("locations")->group(function () {

            //update location logo
            Route::post('/update-logo', [LocationController::class , 'updateLocationLogo']);

            //update location picture
            Route::post('/update-picture', [LocationController::class , 'updateOneOfLocationPictures']);
        });


        Route::prefix("food")->group(function () {
            //update food picture
            Route::post('/update-picture', [FoodController::class , 'updateFoodPicture']);

            //get food statistics data
            Route::get('/statistics/{food_id}', [FoodController::class , 'getFoodStatistics']);

            //delete food ( soft delete )
            Route::delete('/{food_id}', [FoodController::class , 'deleteFood']);

        });


        Route::prefix("drinks")->group(function () {
            //update drink picture
            Route::post('/update-picture', [DrinkController::class , 'updateDrinkPicture']);

            //get drink statistics data
            Route::get('/statistics/{drink_id}', [DrinkController::class , 'getDrinkStatistics']);

            //delete drink ( soft delete )
            Route::delete('/{drink_id}', [DrinkController::class , 'deleteDrink']);

        });


        Route::prefix("accessories")->group(function () {
            //update accessory picture
            Route::post('/update-picture', [AccessoryController::class , 'updateAccessoryPicture']);

            //get accessory statistics data
            Route::get('/statistics/{accessory_id}', [AccessoryController::class , 'getAccessoryStatistics']);

            //delete accessory ( soft delete )
            Route::delete('/{accessory_id}', [AccessoryController::class , 'deleteAccessory']);

        });

    });

});

/*..................APIs for guests.....................*/


//download an event receipt
Route::get('/download-receipt/event-id/{eventId}', [ReceiptController::class, 'downloadReceipt']);



/*...................................<Mansour>...................................*/
Route::middleware([UserMiddleware::class])->group(function () {

    //get all events
    Route::get('/home/events' , [MainEventController::class , 'GetEvents']);

    //get hosts related each event
    Route::get('/home/hosts/{event_id}' , [MainEventHostController::class , 'GetHostsRelatedEvent']);

    //get count of (location - food - drink - accessory - cart)
    Route::get('/home/count' , [LocationController::class , 'HomeCount']);

    //Get Location related host && get location by governorate
    Route::post('/home/sort/location' , [LocationController::class , 'SortLocation']);

    //Get all governorate
    Route::get('/home/location/governorate' , [LocationController::class , 'GetAllGovernorate']);

    //create feedbacks
    Route::post('/location/add/feedback' , [FeedbackController::class , 'CreateFeedback']);

    //show feedback of user
    Route::get('/location/show/feedback/{location_id}' , [FeedbackController::class , 'GetCurrentUserFeedBack']);

    //show rating of location
    Route::get('/location/statistic/rating/{location_id}' , [FeedbackController::class , 'GetLocationStatisticsRate']);

    //show the first three feedbacks related of location and sort feedbacks if(1 star , 2 star , 3 star , 4 star , 5 star)
    Route::post('/location/show/three/feedbacks' , [FeedbackController::class , 'GetFirstThreeFeedback']);

    //show all feedbacks
    Route::get('/location/show/all/feedbacks/{location_id}' , [FeedbackController::class , 'GetAllFeedbacks']);

    //update your feedback
    Route::post('/location/update/feedback' , [FeedbackController::class , 'updateFeedback']);

    //delete your feedback
    Route::delete('/location/delete/feedback/{location_id}' , [FeedbackController::class , 'deleteFeedback']);

    //get items in cart sorted
    Route::post('/location/get/cart/sort' , [CartController::class , 'getCartItemSorted']);

    //get Food category
    Route::get('/location/food/category/{host_id}' , [FoodCategoryController::class , 'getFoodCategory']);

    //get Drink category
    Route::get('/location/drinks/category/{host_id}' , [DrinkCategoryController::class , 'getDrinksCategory']);

    //get Accessory category
    Route::get('/location/accessory/category/{event_id}/{host_id}' , [AccessoryCategoryController::class , 'getAccessoriesCategory']);

    //get food by his category
    Route::post('/location/food/category/sort' , [FoodController::class , 'getFoodByCategorySorted']);

    //get drink by his category
    Route::post('/location/drinks/category/sort' , [DrinkController::class , 'getDrinksByCategorySorted']);

    //get accessory by his category
    Route::post('/location/accessories/category/sort' , [AccessoryController::class , 'getAccessoriesByCategorySorted']);

    //delete all items from cart
    Route::delete('/location/delete/all/cart' , [CartController::class , 'DeleteAllItemsCart']);

    //get user history search
    Route::get('/history/search' , [SearchController::class , 'getSearchHistory']);

    //delete specific search history from search history list
    Route::delete('/delete/one/search/{history_id}' , [SearchController::class , 'deleteOneSearch']);

    //delete all search history of user
    Route::delete('/delete/all/search' , [SearchController::class , 'deleteAllSearch']);

    //api for allowed to user searching
    Route::post('/search' , [SearchController::class , 'Search']);

    //get reservation related user (private - public) and all public reservation type
    Route::post('/get/reservation' , [UserEventController::class , 'getUserEvent']);

    //get details of specific reservation (general)
    Route::get('/get/reservation/myReservation/details/{event_id}' , [UserEventController::class , 'getUserPrivateEventDetails']);

    //get bill of specific reservation
    Route::get('/get/reservation/myReservation/bill/{event_id}' , [UserEventController::class , 'getBill']);

    //get reservation specific food categories for specific event
    Route::get('/get/reservation/myReservation/food/category/{Event_id}' , [HostFoodCategoryController::class , 'getReservationFoodCategory']);

    //get reservation specific drink categories for specific event
    Route::get('/get/reservation/myReservation/drinks/category/{Event_id}' , [HostDrinkCategoryController::class , 'getReservationDrinksCategory']);

    //get reservation specific accessories categories for specific event
    Route::get('/get/reservation/myReservation/accessories/category/{Event_id}' , [MEHACController::class , 'getReservationAccessoriesCategory']);

    //get general info for specific public reservation
    Route::get('/get/reservation/public/general/{event_id}' , [UserEventController::class , 'getGeneralDetails']);

    //get food supplement for specific public reservation
    Route::get('/get/reservation/public/supplement/food/{event_id}' , [EventSupplementController::class , 'getSupplementFood']);

    //get drinks supplement for specific public reservation
    Route::get('/get/reservation/public/supplement/drinks/{event_id}' , [EventSupplementController::class , 'getSupplementDrinks']);

    //get accessories supplement for specific public reservation
    Route::get('/get/reservation/public/supplement/accessories/{event_id}' , [EventSupplementController::class , 'getSupplementAccessories']);

    //join the public event
    Route::get('/reservation/public/join/{event_id}' , [UserJoinedEventController::class , 'joinEvent']);

    //get all location sorted by governorate in home interface
    Route::post('/home/location' , [LocationController::class , 'getAllLocations']);

    //get all food in home interface
    Route::post('/home/food' , [FoodController::class , 'getAllFood']);

    //get all drinks in home interface
    Route::post('/home/drink' , [DrinkController::class , 'getAllDrinks']);

    //get all accessories in home interface
    Route::post('/home/accessory' , [AccessoryController::class , 'getAllAccessories']);

    //get cart in home interface
    Route::post('/home/myCart' , [CartController::class , 'getAllItemCart']);

    //get most location reserved in home interface
    Route::get('/get/location/more/reserved' , [LocationController::class , 'getTheMostLocationReserved']);

    //get user language
    Route::get('/user/language' , [UserController::class , 'getUserLanguage']);

    //get user currency
    Route::get('/user/currency' , [UserController::class , 'getUserCurrency']);

    //get user app rating
    Route::get('/user/app/rate' , [AppRatingController::class , 'getUserAppRating']);

    //add rate to application
    Route::post('/user/add/modify/app/rate' , [AppRatingController::class , 'addAppRate']);

    //delete rate
    Route::delete('/user/delete/rate/{rate_id}' , [AppRatingController::class , 'deleteAppRate']);

});

Route::middleware([AdminMiddleware::class])->group(function () {

    //get name and photo of current admin or owner
    Route::get('/web/home/current' , [ProfileController::class , 'WebGetCurrentAdmin']);

    //search of admin
    Route::post('/web/home/search' , [AdminController::class , 'WebSearchAdmin']);

    //Web Three Statistic in down
    Route::get('/web/home/three/statistic' , [AdminController::class , 'WebThreeStatistic']);

    //get home counts
    Route::get('/web/home/counts' , [AdminController::class , 'WebCounts']);

    //get numbers of events for each month
    Route::get('/web/home/event/chart' , [UserEventController::class , 'WebEventGraphicalStatistics']);

    //get owners
    Route::get('/web/home/owners' , [OwnerController::class , 'WebGetOwners']);

    //get admins
    Route::get('/web/home/admins' , [AdminController::class , 'WebGetAdmins']);

    //get hosts to filter location according to host related it
    Route::get('/web/home/statistic/hosts' , [HostController::class , 'WebGetHosts']);

    //get location related of each host
    Route::get('/web/home/statistic/hosts/locations/{host_id}' , [HostController::class , 'GetLocationRelatedHost']);

    //get feedbacks related of every location
    Route::get('/web/home/statistic/hosts/locations/feedbacks/{location_id}' , [FeedbackController::class , 'WebGetFeedBackByLocation']);

    //filter all users download application by date registration
    Route::post('/web/home/statistic/download' , [UserController::class , 'WebGetUserDownloadApp']);

    //get the user profile details
    Route::get('/web/home/statistic/download/profile/{user_id}' , [UserController::class , 'GetUserProfileDetails']);

    //get all warehouses and sort it by governorate
    Route::post('/web/home/statistic/warehouse' , [WarehouseController::class , 'GetWarehouseByGovernorate']);

    //get all reservation by date
    Route::post('/get/reservation/byDate' , [UserEventController::class , 'getReservationByDate']);

    //get all reservation by host
    Route::post('/get/reservation/byHost' , [UserEventController::class , 'getReservationByHost']);

    //get all reservation by governorate
    Route::post('/get/reservation/byGovernorate' , [UserEventController::class , 'getReservationByGovernorate']);

    //get all governorate by state
    Route::post('/get/reservation/byState' , [UserEventController::class , 'getReservationByState']);

    //get my reservation (owner - admin)
    Route::get('/get/reservation/mine' , [UserEventController::class , 'getMineReservation']);

    //get specific reservation general details
    Route::get('/get/reservation/details/general/{event_id}' , [UserEventController::class , 'getUserGeneralEventDetails']);

    // get specific reservation bill
    Route::get('/get/reservation/details/bill/{event_id}' , [UserEventController::class , 'getBill']);

    //get statistics of user experience
    Route::get('/get/statistics/UserExperience' , [UserController::class , 'StatisticsUserExperience']);

    //get statistics of reservation
    Route::get('/get/statistics/reservation' , [UserEventController::class , 'StatisticsReservation']);

    //get statistics of sales
    Route::get('/get/statistics/sales' , [OwnerController::class , 'StatisticsSales']);

    //get statistics of profits
    Route::get('/get/statistics/profits' , [OwnerController::class , 'StatisticsProfits']);

    //get statistics of masculinity
    Route::get('/get/statistics/masculinity' , [UserController::class , 'StatisticsMasculinity']);

    //get statistics of femininity
    Route::get('/get/statistics/femininity' , [UserController::class , 'StatisticsFemininity']);

    //get statistics of app rating
    Route::get('/get/statistics/Rating' , [OwnerController::class , 'StatisticsRating']);

    //get locations by host (category : 0 -> 7)
    Route::get('/get/location/by/host/{category_id}' , [LocationController::class , 'WebGetLocationByHost']);

    //get locations counts
    Route::get('/get/location/count' , [StatisticsController::class , 'WebGetLocationCount']);

    //get specific location details (general)
    Route::get('/get/location/general/{location_id}' , [LocationController::class , 'WebGetLocationGeneral']);

    //get specific location details (details)
    Route::get('/get/location/details/{location_id}' , [LocationController::class , 'WebGetLocationDetails']);

    //get contact information for specific location
    Route::get('/get/location/ContactInformation/{location_id}' , [LocationController::class , 'WebGetLocationAdmin']);

    //get available admin for nwe location
    Route::get('/get/location/available/admins' , [AdminController::class , 'GetAvailableAdmin']);

    //get food by category
    Route::get('/get/food/by/category/{category_id}' , [FoodController::class , 'WebGetFoodByCategory']);

    //get food count
    Route::get('/get/food/count' , [FoodController::class , 'WebGetFoodCount']);

    //get specific food details (general)
    Route::get('/get/food/general/{food_id}' , [FoodController::class , 'WebGetFoodGeneral']);

    //get specific food details (details)
    Route::get('/get/food/details/{food_id}' , [FoodController::class , 'WebGetFoodDetails']);

    //get drinks by category
    Route::get('/get/drinks/by/category/{drink_id}' , [DrinkController::class , 'WebGetDrinksByCategory']);

    //get drinks count
    Route::get('/get/drinks/count' , [DrinkController::class , 'WebGetDrinksCount']);

    //get specific drink details (general)
    Route::get('/get/drinks/general/{drink_id}' , [DrinkController::class , 'WebGetDrinksGeneral']);

    //get specific drink details (details)
    Route::get('/get/drinks/details/{drink_id}' , [DrinkController::class , 'WebGetDrinksDetails']);

    //get all accessory by category and warehouse
    Route::get('/get/accessories/by/category/{accessory_id}/{warehouse_id}' , [AccessoryController::class , 'WebGetAccessoriesByCategory']);

    //get accessories count
    Route::get('/get/accessories/count' , [AccessoryController::class , 'WebGetAccessoriesCount']);

    //get specific drink details (general)
    Route::post('/get/accessories/general' , [AccessoryController::class , 'WebGetAccessoriesGeneral']);

    //get specific drink details (details)
    Route::post('/get/accessories/details' , [AccessoryController::class , 'WebGetAccessoryDetails']);

    //get profile of admin or owner
    Route::get('/admin_owner/get/profile' , [ProfileController::class , 'getAdminOwnerProfile']);

    //edit profile of admin or owner
    Route::post('/admin_owner/edit/profile' , [ProfileController::class , 'editAdminOwnerProfile']);

    //get all old admin to replace it
    Route::get('/profile/helper/select/old_admin' , [AdminController::class , 'getAllAdmin']);

    //get all new admin to replace it
    Route::get('/profile/helper/select/new_admin' , [AdminController::class , 'getAllNewAdmin']);

    //get all location for replacing
    Route::get('/profile/helper/select/location' , [LocationController::class , 'getAllLocationsSelect']);

});

Route::middleware([OwnerMiddleware::class])->group(function () {

    //deleted user feedback offensive from owner
    Route::delete('/web/home/statistic/feedbacks/delete/{feedback_id}' , [OwnerController::class , 'WebDeleteFeedback']);

    //owner block user
    Route::post('/web/home/blockUser/{user_id}' , [OwnerController::class , 'blockUser']);

    //owner unblock user
    Route::get('/web/home/unBlockUser/{user_id}' , [OwnerController::class , 'unblockUser']);

    //edit specific location details
    Route::post('/edit/location/details/{location_id}' , [LocationController::class , 'WebEditLocationDetails']);

    //delete specific location
    Route::delete('/delete/location/{location_id}' , [LocationController::class , 'WebDeleteLocation']);

    //put location in Maintenance
    Route::get('/edit/location/Maintenance/{location_id}' , [LocationController::class , 'WebPutLocationInMaintenance']);

    //put location in Service
    Route::get('/edit/location/Service/{location_id}' , [LocationController::class , 'WebPutLocationInService']);

    //add new location
    Route::post('/add/location' , [LocationController::class , 'WebAddLocation']);

    //edit specific food details
    Route::post('/edit/food/details/{food_id}' , [FoodController::class , 'WebEditFoodDetails']);

    //add new food
    Route::post('/add/food' , [FoodController::class , 'WebAddFood']);

    //get specific drink details
    Route::post('/edit/drinks/details/{drink_id}' , [DrinkController::class , 'WebEditDrinksDetails']);

    //add new location
    Route::post('/add/drink' , [DrinkController::class , 'WebAddDrink']);

    //edit specific drink details
    Route::post('/edit/accessories/details' , [AccessoryController::class , 'WebEditAccessoriesDetails']);

    //add new accessory
    Route::post('/add/accessory' , [AccessoryController::class , 'WebAddAccessory']);

    //update admin location
    Route::post('/update/admin/location' , [OwnerController::class , 'updateAdminLocation']);

    //add new admin
    Route::post('/add/admin' , [OwnerController::class , 'AddNewAdmin']);

    //recharge user wallet
    Route::post('/recharge/user/wallet' , [OwnerController::class , 'rechargeWallet']);

});



//test
//Route::get('/notification/user/{user_id}' ,[TestsController::class, 'testNotifications']);
//Route::get('/translate' ,[TestsController::class, 'testTranslation']);
//Route::get('/get-gender', [TestsController::class, 'getGender']);
//Route::get('/change-currency', [TestsController::class, 'convertPrice']);

Route::post('/send-notification', [TestsController::class, 'sendPushNotification']);




Route::get('/test-notification', function (NotificationService $notificationService) {
    $user = User::first(); // Replace with actual user retrieval
    $title = 'Test Notification';
    $message = 'This is a test notification';

    $result = $notificationService->send($user, $title, $message);

    return $result ? 'Notification sent successfully' : 'Failed to send notification';
});
