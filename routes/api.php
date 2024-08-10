<?php


use App\Http\Controllers\AccessoryCategoryController;
use App\Http\Controllers\AccessoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DrinkCategoryController;
use App\Http\Controllers\DrinkController;
use App\Http\Controllers\EventSupplementController;
use App\Http\Controllers\FavoriteController;
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
use App\Http\Controllers\WarehouseController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\OwnerMiddleware;
use App\Http\Middleware\UserMiddleware;
use App\Models\UserJoinedEvent;
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

        //get weekly report as PDF
        Route::get('/weekly-report', [StatisticsController::class, 'getWeeklyReport']);

    });

});


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

    //get count of (location - food - drink - accessory)
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
});

Route::middleware([OwnerMiddleware::class])->group(function () {

    //deleted user feedback offensive from owner
    Route::delete('/web/home/statistic/feedbacks/delete/{feedback_id}' , [OwnerController::class , 'WebDeleteFeedback']);

    //owner block user
    Route::post('/web/home/blockUser/{user_id}' , [OwnerController::class , 'blockUser']);

    //owner unblock user
    Route::get('/web/home/unBlockUser/{user_id}' , [OwnerController::class , 'unblockUser']);

});

//done without translate

//my reservation (git reservation - general of each reservation - bill of each reservation)
Route::post('/get/reservation' , [UserEventController::class , 'getUserEvent']);
Route::get('/get/reservation/myReservation/details/{event_id}' , [UserEventController::class , 'getUserPrivateEventDetails']);
Route::get('/get/reservation/myReservation/bill/{event_id}' , [UserEventController::class , 'getBill']);

//my reservation add food - drink - accessory (get category of each reservation)
Route::get('/get/reservation/myReservation/food/category/{Event_id}' , [HostFoodCategoryController::class , 'getReservationFoodCategory']);
Route::get('/get/reservation/myReservation/drinks/category/{Event_id}' , [HostDrinkCategoryController::class , 'getReservationDrinksCategory']);
Route::get('/get/reservation/myReservation/accessories/category/{Event_id}' , [MEHACController::class , 'getReservationAccessoriesCategory']);


//public reservation (general , supplement(food-drink-accessory) , join event)
Route::get('/get/reservation/public/general/{event_id}' , [UserEventController::class , 'getGeneralDetails']);
Route::get('/get/reservation/public/supplement/food/{event_id}' , [EventSupplementController::class , 'getSupplementFood']);
Route::get('/get/reservation/public/supplement/drinks/{event_id}' , [EventSupplementController::class , 'getSupplementDrinks']);
Route::get('/get/reservation/public/supplement/accessories/{event_id}' , [EventSupplementController::class , 'getSupplementAccessories']);
Route::get('/reservation/public/join/{event_id}' , [UserJoinedEventController::class , 'joinEvent']);

//web
//done without translate

//get reservation sorted by (date - host - governorate - status - my reservation) and get each reservation details (general - bill)
Route::post('/get/reservation/byDate' , [UserEventController::class , 'getReservationByDate']);
Route::post('/get/reservation/byHost' , [UserEventController::class , 'getReservationByHost']);
Route::post('/get/reservation/byGovernorate' , [UserEventController::class , 'getReservationByGovernorate']);
Route::post('/get/reservation/byState' , [UserEventController::class , 'getReservationByState']);
Route::get('/get/reservation/mine' , [UserEventController::class , 'getMineReservation']);
Route::get('/get/reservation/details/general/{event_id}' , [UserEventController::class , 'getUserGeneralEventDetails']);
Route::get('/get/reservation/details/bill/{event_id}' , [UserEventController::class , 'getBill']);


//done without translate

//get statistics of web (all cards and 3 chart)
Route::get('/get/statistics/UserExperience' , [UserController::class , 'StatisticsUserExperience']);
Route::get('/get/statistics/reservation' , [UserEventController::class , 'StatisticsReservation']);
Route::get('/get/statistics/sales' , [OwnerController::class , 'StatisticsSales']);
Route::get('/get/statistics/profits' , [OwnerController::class , 'StatisticsProfits']);
Route::get('/get/statistics/masculinity' , [UserController::class , 'StatisticsMasculinity']);
Route::get('/get/statistics/femininity' , [UserController::class , 'StatisticsFemininity']);
Route::get('/get/statistics/Rating' , [OwnerController::class , 'StatisticsRating']);

//web location

//location - 1
Route::get('/get/location/by/host/{category_id}' , [LocationController::class , 'WebGetLocationByHost']);
Route::get('/get/location/count' , [StatisticsController::class , 'WebGetLocationCount']);

//location - 2
Route::get('/get/location/general/{location_id}' , [LocationController::class , 'WebGetLocationGeneral']);
Route::get('/get/location/details/{location_id}' , [LocationController::class , 'WebGetLocationDetails']);
Route::post('/edit/location/details/{location_id}' , [LocationController::class , 'WebEditLocationDetails']);
Route::get('/get/location/ContactInformation/{location_id}' , [LocationController::class , 'WebGetLocationAdmin']);
Route::delete('/delete/location/{location_id}' , [LocationController::class , 'WebDeleteLocation']);
Route::get('/edit/location/Maintenance/{location_id}' , [LocationController::class , 'WebPutLocationInMaintenance']);
Route::get('/edit/location/Service/{location_id}' , [LocationController::class , 'WebPutLocationInService']);

//location - 3
Route::post('/add/location' , [LocationController::class , 'WebAddLocation']);
Route::get('/get/location/available/admins' , [AdminController::class , 'GetAvailableAdmin']);

//web food

//food - 1
Route::get('/get/food/by/category/{category_id}' , [FoodController::class , 'WebGetFoodByCategory']);
Route::get('/get/food/count' , [FoodController::class , 'WebGetFoodCount']);

//food - 2
Route::get('/get/food/general/{food_id}' , [FoodController::class , 'WebGetFoodGeneral']);
Route::get('/get/food/details/{food_id}' , [FoodController::class , 'WebGetFoodDetails']);
Route::post('/edit/food/details/{food_id}' , [FoodController::class , 'WebEditFoodDetails']);

//food - 3
Route::post('/add/food' , [FoodController::class , 'WebAddFood']);


//web drink

//drink - 1
Route::get('/get/drinks/by/category/{drink_id}' , [DrinkController::class , 'WebGetDrinksByCategory']);
Route::get('/get/drinks/count' , [DrinkController::class , 'WebGetDrinksCount']);

//drink - 2
Route::get('/get/drinks/general/{drink_id}' , [DrinkController::class , 'WebGetDrinksGeneral']);
Route::get('/get/drinks/details/{drink_id}' , [DrinkController::class , 'WebGetDrinksDetails']);
Route::post('/edit/drinks/details/{drink_id}' , [DrinkController::class , 'WebEditDrinksDetails']);

//drink - 3
Route::post('/add/drink' , [DrinkController::class , 'WebAddDrink']);

//test
//Route::get('/notification/user/{user_id}' ,[TestsController::class, 'testNotifications']);
//Route::get('/translate' ,[TestsController::class, 'testTranslation']);
//Route::get('/get-gender', [TestsController::class, 'getGender']);
//Route::get('/change-currency', [TestsController::class, 'convertPrice']);
