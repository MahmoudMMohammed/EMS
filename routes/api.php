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
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MainEventController;
use App\Http\Controllers\MainEventHostController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\TestsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEventController;
use App\Http\Controllers\WarehouseController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\OwnerMiddleware;
use App\Http\Middleware\UserMiddleware;
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
Route::post('login', [AuthController::class, 'login']);

//login for admin
Route::post('login/admin', [AuthController::class, 'login']);



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




    //getting user general info
    Route::get('/user/profile/general', [ProfileController::class, 'getGeneralInfo']);

    //updating user general info
    Route::post('/user/profile/general', [ProfileController::class , 'updateProfileGeneralInfo']);

    //updating user profile picture
    Route::post('/user/profile/picture', [ProfileController::class , 'updateProfilePicture']);

    //getting user privacy info
    Route::get('/user/profile/privacy', [ProfileController::class, 'getPrivacyInfo']);

    //updating user privacy info
    Route::post('/user/profile/privacy', [ProfileController::class , 'updateProfilePrivacyInfo']);

    //updating user preferred language
    Route::post('/user/profile/language', [ProfileController::class , 'updatePreferredLanguage']);

    //get user favorites
    Route::get('/user/profile/favorites', [FavoriteController::class, 'getUserFavorites']);

    //add location to user favorites
    Route::post('/user/profile/favorites', [FavoriteController::class , 'addToFavorites']);

    //remove location from user favorites
    Route::post('/user/profile/favorites/remove', [FavoriteController::class , 'removeFromFavorites']);

    //delete user account
    Route::delete('/user/delete-account', [ProfileController::class , 'deleteAccount']);




    //add item to user cart
    Route::post('/user/cart/add-items', [CartController::class , 'addToCart']);

    //remove item from user cart
    Route::post('/user/cart/remove-item', [CartController::class , 'removeFromCart']);

    //update item quantity in cart
    Route::post('/user/cart/update-item', [CartController::class , 'updateCartQuantity']);




    //create an event for user
    Route::post('/user/events', [UserEventController::class , 'createEvent']);

    //get event by its id
    Route::get('/user/events/{event_id}', [UserEventController::class , 'getEventById']);

    //get all events for user
    Route::get('/user/events', [UserEventController::class , 'getUserEvents']);




    //get supplements for user event
    Route::get('/user/events/{event_id}/supplements', [EventSupplementController::class , 'getSupplementsForEvent']);

    //add food and drinks to user's event supplements
    Route::post('/user/events/{event_id}/food&drinks', [EventSupplementController::class , 'processFoodAndDrinksSupplements']);

    //add accessories to user's event supplements
    Route::post('/user/events/{event_id}/accessories', [EventSupplementController::class , 'processAccessoriesSupplements']);

    //get the declined food and drinks for user
    Route::get('/user/cart/declined_food&drinks',[EventSupplementController::class, 'getDeclinedFoodAndDrinks']);

    //get the declined accessories for user
    Route::get('/user/cart/declined_accessories',[EventSupplementController::class, 'getDeclinedAccessories']);





    //explore all locations available
    Route::get('/locations', [LocationController::class, 'getLocations']);

    //find location by its id
    Route::get('/locations/{location_id}', [LocationController::class, 'getLocationById']);



    //explore all available food categories
    Route::get('/food/categories', [FoodController::class, 'getFoodCategories']);

    //explore all food for specific category
    Route::get('/food/category/{category_id}', [FoodController::class, 'getFoodByCategory']);

    //find food by its id
    Route::get('/food/{food_id}', [FoodController::class, 'getFoodById']);




    //explore all available drinks categories
    Route::get('/drinks/categories', [DrinkController::class, 'getDrinksCategories']);

    //explore all drinks for specific category
    Route::get('/drinks/category/{category_id}', [DrinkController::class, 'getDrinksByCategory']);

    //find drink by its id
    Route::get('/drinks/{drink_id}', [DrinkController::class, 'getDrinkById']);





    //explore all available drinks categories
    Route::get('/accessories/categories', [AccessoryController::class, 'getAccessoriesCategories']);

    //explore all accessories for specific category
    Route::get('/accessories/category/{category_id}', [AccessoryController::class, 'getAccessoriesByCategory']);

    //find accessory by its id
    Route::get('/accessories/{accessory_id}', [AccessoryController::class, 'getAccessoryById']);


    //generate QR code for event receipt
    Route::get('/user/event/{userEventId}/qr', [ReceiptController::class, 'generateQRForReceipt']);



});

Route::middleware([AdminMiddleware::class])->group(function () {

});

Route::middleware([OwnerMiddleware::class])->group(function () {

});

/*..................APIs for guests.....................*/


//download an event receipt
Route::get('/download-receipt', [ReceiptController::class, 'downloadReceipt']);



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




//test
//Route::get('/notification/user/{user_id}' ,[TestsController::class, 'testNotifications']);
//Route::get('/translate' ,[TestsController::class, 'testTranslation']);
//Route::get('/get-gender', [TestsController::class, 'getGender']);
