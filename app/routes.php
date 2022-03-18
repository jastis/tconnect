<?php
declare(strict_types=1);

use App\Application\Actions\User\UserAction;
use App\Application\Actions\User\PageAction;
use App\Application\Actions\Product\ProductAction;
use App\Application\Actions\Product\SellerAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Views\Twig;
use App\Application\Middleware\UserAuthMiddleware;
use App\Application\Middleware\SellerAuthMiddleware;
use App\Application\Middleware\AuthHeaderMiddleware;
use Slim\Routing\RouteContext;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/',PageAction::class .':homepage')->setName('home');
    $app->get('/{category}',PageAction::class .':searchpage')->setName('searchhome');
    //$app->get('/search',PageAction::class .':searchQuery')->setName('searchQuery');
    //$app->get('/{category}',ProductAction::class .':getCatTree')->setName('searchhome');
  

     $app->group('/ui', function (Group $group) {
        $group->get('/', PageAction::class .':homepage')->setName('home');
        $group->get('/dashboard', PageAction::class .':dashboard')->setName('dashboard')->add(SellerAuthMiddleware::class);
        $group->get('/register', PageAction::class .':registerNewUser')->setName('registeruser');
        $group->get('/edituser', PageAction::class .':editUser')->setName('edituser')->add(UserAuthMiddleware::class);
        $group->get('/login', PageAction::class .':login')->setName('login');
        $group->get('/product-details/{pid}', PageAction::class .':single_product')->setName('single-product');
        $group->get('/cart', PageAction::class .':cart')->setName('cart');
        $group->get('/checkout', PageAction::class .':checkout')->setName('checkout');
        $group->get('/logout', PageAction::class .':logOutUser')->add(UserAuthMiddleware::class)->setName('logout');
        $group->get('/test', UserAction::class .':testQuery');
        $group->get('/help', PageAction::class .':getHelp');
        $group->get('/privacy', PageAction::class .':privacyPolicy');
        $group->get('/delivery', PageAction::class .':deliveryPage')->add(UserAuthMiddleware::class);
        $group->get('/verify_mail/{ucode}', UserAction::class .':enableUserByUserID');
        

        

    });

    $app->group('/api/v1', function (Group $group) {
        $group->GET('/category', ProductAction::class .':getCategories');//->add(AuthHeaderMiddleware::class);   // get all categories -
        $group->GET('/brand', ProductAction::class .':getBrands')->add(AuthHeaderMiddleware::class);          //get all brands  -
        $group->GET('/product', ProductAction::class .':getProducts'); //->add(AuthHeaderMiddleware::class);    //get all products -
        $group->GET('/category/product/group', ProductAction::class .':getGroupedCategories')->add(AuthHeaderMiddleware::class);   // get all products grouped by categories
        $group->GET('/product/category/{cat_id}', ProductAction::class .':getProductsByCategory')->add(AuthHeaderMiddleware::class);// get product by cat
        $group->GET('/product/brand/{br_id}', ProductAction::class .':getProductsByBrand')->add(AuthHeaderMiddleware::class); // get all products by brands
        $group->GET('/product/{pid}', ProductAction::class .':getSingleProduct')->add(AuthHeaderMiddleware::class); //get single products -
        $group->POST('/cart', ProductAction::class .':getAppCartItem')->add(AuthHeaderMiddleware::class);       // add/remove item to cart
        $group->POST('/cart/update', ProductAction::class .':updateAppCartItem')->add(AuthHeaderMiddleware::class);       // add/remove item to cart
        $group->POST('/cart/remove', ProductAction::class .':removeAppCartItem')->add(AuthHeaderMiddleware::class);   // remove item from cart
        $group->POST('/user/create', UserAction::class .':registerUser');   // register new user -
        $group->POST('/user/login', UserAction::class .':appLogin');   //  login user -
        $group->POST('/order/post', ProductAction::class .':placeOrder');   //  login user -
        $group->POST('/user/forgot_pw', UserAction::class .':resetPasswordByMail'); 
        $group->POST('/user/reset_pw', UserAction::class .':updatePasswordById')->add(AuthHeaderMiddleware::class); 
        

    });

    $app->group('/users', function (Group $group) {
        $group->POST('/registerUser', UserAction::class .':registerNewUser');
        $group->POST('/login', UserAction::class .':loginUser');
        $group->GET('/getstates/{cid}', UserAction::class .':getAllStates');
        $group->GET('/getcities/{sid}', UserAction::class .':getAllCities');
        $group->POST('/editUser', UserAction::class .':updateUserProfile');
        $group->POST('/changePassword', UserAction::class .':updatePasswordById');
        $group->POST('/resetPassword', UserAction::class .':resetPasswordByMail');
        $group->GET('/switchview', UserAction::class .':switchBuyerMode');
        
       
    });

    $app->group('/product', function (Group $group) {
        $group->GET('/cart', ProductAction::class .':getCart');
        $group->GET('/gcart', ProductAction::class .':getGroupedCart');
        $group->GET('/product/{pid}', ProductAction::class .':getSingleProduct');
        $group->GET('/compare/{pname}', ProductAction::class .':compareProduct');
        $group->POST('/review/add', ProductAction::class .':addReview');
        $group->POST('/coupon/apply', ProductAction::class .':applyCoupon');


      

     
       
    });

    $app->group('/seller', function (Group $group) {
        $group->GET('/product', SellerAction::class .':getAllProduct');
        $group->GET('/product/{pid}', SellerAction::class .':getSingleProduct');
        $group->GET('/load/category', SellerAction::class .':loadCatNew');
        $group->GET('/load/brand', SellerAction::class .':loadBrandNew');
        $group->GET('/load/product', SellerAction::class .':loadProductNew');
        $group->GET('/load/upload_product', SellerAction::class .':loadUploadProduct');
        $group->GET('/load/productlist', SellerAction::class .':loadProducts');
        $group->GET('/load/orderlist', SellerAction::class .':loadOrders');
        $group->GET('/load/shipping_create', SellerAction::class .':loadCreateShipping');
        $group->GET('/load/account', SellerAction::class .':loadCreateAccount');
        $group->GET('/load/transactions', SellerAction::class .':getTransactions');
        $group->GET('/load/users', SellerAction::class .':getInActiveUsers');
        $group->POST('/product/create', SellerAction::class .':createProduct');
        $group->POST('/product/upload', SellerAction::class .':uploadProduct');
        $group->POST('/category/create', SellerAction::class .':createCategory');
        $group->POST('/brand/create', SellerAction::class .':createBrand');
        $group->GET('/load/category/{cid}', SellerAction::class .':loadCatNew');
        $group->GET('/load/brand/{bid}', SellerAction::class .':loadBrandNew');
        $group->GET('/load/product/{pid}', SellerAction::class .':loadProductNew');
        $group->GET('/delete/product/{pid}', SellerAction::class .':deleteProduct');

        $group->GET('/user/deactivate/{user_id}', SellerAction::class .':disableUser');
        $group->GET('/user/activate/{user_id}', SellerAction::class .':enableUser');

        $group->GET('/view/order/{order}', SellerAction::class .':getOrderContentBySeller');
        $group->GET('/ship/order/{oid}/{status}', SellerAction::class .':shipOrder');
        $group->POST('/chart/monthly', SellerAction::class .':getChartMonthly');
        $group->POST('/chart/daily', SellerAction::class .':getChartDaily');
        $group->POST('/shipping/create', SellerAction::class .':createShipping');
        $group->POST('/area/delete/{area_id}', SellerAction::class .':deleteShippingRate');
        $group->POST('/bankdetails/create', SellerAction::class .':createBankDetails');
        $group->POST('/tranaction/confirm', SellerAction::class .':confirmTransactions');
        $group->POST('/delivery/confirm', SellerAction::class .':deliveryConfirm')->add(UserAuthMiddleware::class);  
        $group->GET('/listsellers/{oid}', SellerAction::class .':listSeller');
        $group->POST('/agent/proof_upload', SellerAction::class .':addAgentProof');
        $group->GET('/getsolditems/{oid}/{sid}', SellerAction::class .':getGroupedSoldItems');
        $group->POST('/coupon/create', SellerAction::class .':createCoupon');
        $group->GET('/coupon/delete/{cp_id}', SellerAction::class .':deleteCoupon');
        $group->GET('/coupon', SellerAction::class .':getCouponBySeller');

        
        


        

    });

    $app->group('/cart', function (Group $group) {
        $group->GET('/addcart/{pid}', ProductAction::class .':addCart');
        $group->GET('/remove/{pid}', ProductAction::class .':removeCartItem');
        $group->GET('/basketremove/{pid}', ProductAction::class .':removeItemFromBasket');
        $group->GET('/update/{pid}/{qty}', ProductAction::class .':updateCartItem');
        $group->POST('/checkout', ProductAction::class .':placeOrder');
        $group->POST('/getshippingrate', ProductAction::class .':getShippingRate');
       
     
       
    });


};
