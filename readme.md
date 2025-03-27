# API for an online store (E-commerce API)
This project is built using API Platform and Symfony, and it is an API for
ecommerce service. It includes various features such as Stripe Payment,
Coupon system (for discounts), File upload to Amazon S3 Cloud Storage, and more.

> #### Technology Used
Programming language: `PHP`

Frameworks: `Symfony`, `API Platform`

## Setting up 
This repository holds the code for API of ecommerce store developed
using API Platform Version 4, and Symfony Version 7.

### Download composer dependencies
Make sure you have composer installed and then run
```
composer install
```

### Database Setup
The code comes with all the necessary code that can generate database
tables, so make sure you have PHP and MySQL up and running after that 
you can use the following commands to set up the database for this project.

```
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
```

### Start up the Symfony web server
To run the project using Symfony web server, run the command:

```
symfony serve
```

## API Endpoints
### Login
Request sent to this endpoint will return a token 
which can then be used to send request to other endpoints.

<pre><b>POST</b> /api/login</pre>

#### Example (sending request using JavaScript)
```
const user = await fetch('/api/login', {
  method: 'POST',
  headers: {
    'Content-Type' : 'application/json',
  },
  body: JSON.stringify({
    email    : 'EMAIL_ADDRESS',
    password : 'PASSWORD',
  })
});
```

* Request body
    * ```email```: ```string```
    * ```password```: ```string```

* Content-Type
  * ```application/json```

* Response
    * ```{ "token": "TOKEN_STRING" }```

***

# Cart
We can add item(s) to the cart,
remove item, Delete Item from the cart, and update
the quantity of a product in the cart.

Different operations related to cart is presented below.

> #### 1. GET
Fetch single cart by cart id.

**Example**
<pre><b>GET</b> /api/carts/{cartId}</pre>

**<u>Description</u>**

```{cartId}``` refers to the cart id.
If we want to get the cart with id ```1```, then we can send ```GET``` request to: ```/api/carts/1```.

**Example Response**

```
{
    "id": 1,
    "owner": "/api/users/1",
    "status": "active",
    "totalPrice": "200.00",
    "cartItems": [
        "/api/cart_items/59"
    ],
    "createdAt": "2025-03-09T23:43:16+00:00"
}
```

**Response** ```200 OK```

**<u>Authentication Required</u>: YES**

* ```Authorization: Bearer <token>```
    * ```type```: ```Bearer```
    * ```<token>```: ```The actual token```

**<u>Working overview</u>**
- If we send a ```GET``` request, cart that belongs to the logged-in user
  is returned.
- If we try to fetch a cart that does not belong to the logged-in user,
  we get following response:

  **<u>Status</u>**

  ```404 Not Found```<br /><br />

  **<u>Response</u>**
  ```
  {
      "status": 404,
      "error": "Resource not found",
      "message": "Not Found"
  }
  ```


***

> #### 2. GET Collection
Fetches carts available in the system.

**Example**
<pre><b>GET</b> /api/carts</pre>

**<u>Description</u>**

Fetches all the carts.

**Example Response (For ```Admin``` and ```Cart Owner```)**

Collection Response
```
[
    {
        "id": 1,
        "owner": "/api/users/1",
        "status": "active",
        "totalPrice": "200.00",
        "cartItems": [
            "/api/cart_items/1"
        ],
        "createdAt": "2025-03-09T23:43:16+00:00"
    }
]
```

**Response** ```200 OK```

**<u>Authentication Required</u>: YES**

* ```Authorization: Bearer <token>```
    * ```type```: ```Bearer```
    * ```<token>```: ```The actual token```

**<u>Working overview</u>**
- If we send a ```GET``` request, ```all the available carts``` in the system
  gets fetched (Only ```admin``` user can see ```all the available carts``` in the system).
- If a user (i.e. ```logged-in``` user) tries to fetch ```carts``` using this
  endpoint but the ```logged-in``` user is not the owner of the cart then in such
  case, ```[]``` is returned.
- If a user (i.e. ```logged-in``` user) tries to fetch ```carts``` using this
  endpoint and the ```logged-in``` user is the owner of the cart, then in such
  case, all the carts that belongs to the ```logged-in``` user is returned.

***

> #### 3. POST
Adds item(s) to the cart.

<pre><b>POST</b> /api/carts</pre>

**Authentication Required: YES**

The endpoint ```/api/carts``` requires user to be logged-in before
they can add item to the cart.

* ```Authorization: Bearer <token>```
    * ```type```: ```Bearer```
    * ```<token>```: ```The actual token```


**Example of JSON body**
```
{
    "product": "/api/products/{productId}",
    "quantity": 10
}
```

**Content-Type**

```application/json```

**Description of fields**

The request body contains the following fields:
  - ```product```: **(string)**, IRI of a product
  - ```quantity```: **(int)**, the quantity of the product
  - ```{productId}``` refers to the product id that will be added to the cart

**Working overview**
- In the above <u>```Example of JSON body```</u> section, we're adding
    product with a specific ```{productId}``` with a
    quantity ```10``` which adds ```10``` products of a
    specific ```{productId}``` to the cart.


- If we send request twice for the same data (product) in the request body, the quantity gets
    added (summed up) for the same product in the same cart.

***

# Cart Item

Cart Item is associated to the ```cart```. Each ```cart item``` 
holds details about item that is added to the cart.

**<u>All the endpoints requires authentication.</u>**

> #### 1. GET
Fetches cart item.

<pre><b>GET</b> /api/cart_items/{cartItemId}</pre>

**<u>Description:</u>**

Fetches all the cart items of a ```logged-in``` user.

```{cartItemId}``` refers to the id of the cart item that is being fetched using the
```GET``` request.

**<u>Authentication Required:</u> YES**

**<u>Example Response:</u>**

If a user is ```logged-in``` and the cart_item belongs to the ```logged-in```
user, then similar kind of response is returned:

```
{
    "cart": "/api/carts/1",
    "product": "/api/products/1",
    "quantity": 2,
    "price_per_unit": "20.00",
    "totalPrice": "40.00"
}
```

**<u>Status:</u>** ```200 OK```

If Admin ```logs-in``` into the system, then isAdmin field is also gets returned
along with other fields.

```
{
    "cart": "/api/carts/1",
    "product": "/api/products/1",
    "quantity": 2,
    "price_per_unit": "20.00",
    "totalPrice": "40.00"
    "isAdmin": true
}
```


If a user is ```logged-in``` but the cart item does not belong to the 
same ```logged-in``` user, then following response is returned.
```
{
    "status": 404,
    "error": "Resource not found",
    "message": "Not Found"
}
```
**<u>Status:</u>** ```404 Not Found```

If non-logged in user tries to send request to this route, ```401 Unauthorized```
response is returned.

```
{
    "statusCode": 401,
    "success": false,
    "message": "Full authentication is required to access this resource."
}
```

**<u>Status:</u>** ```401 Unauthorized```

***

> #### 2. GET Collection

Fetch cart items.

<pre><b>GET</b> /api/cart_items</pre>

**<u>Example Response:</u>**

This is the response of the collection of the cart items of the ```logged-in``` user to whom
the cart items belongs to.

```
[
    {
        "cart": "/api/carts/1",
        "product": "/api/products/1",
        "quantity": 2,
        "price_per_unit": "20.00",
        "totalPrice": "40.00"
    }
]
```

**<u>Status:</u>** ```200 OK```


If ```logged-in``` user is not the owner of cart items, we get following response:
```
[]
``` 
**<u>Status:</u>** ```200 OK```

**<u>Authentication Required:</u> YES**

If we try to fetch without authenticating, we get the following response:
```
{
    "statusCode": 401,
    "success": false,
    "message": "Full authentication is required to access this resource."
}
```

**<u>Status:</u> ```401 Unauthorized```**



***

> #### 3. PATCH

Updates the cart item.

<pre><b>PATCH</b> /api/cart_items/{cartItemId}</pre>

**<u>Description:</u>**
  - ```{cartItemId}``` refers to the cart item id.

**<u>Example of JSON body:</u>**
```
{
    "quantity": 50
}
```

If we try to update product ```to a different product``` by sending product in the request, 

```
{
    "quantity": 2,
    "product": "/api/products/91"
}
```
We're trying to change the product in the ```cart item``` <u>```which is not
allowed```</u>. We can only update the quantity of product in the cart item.

we get the following response if we send above 
fields in the request i.e. ```product``` field with ```different product IRI``` than that is already in the ```cart item```:
```
{
    "statusCode": 400,
    "status": "Bad Request",
    "message": "error",
    "errors": [],
    "description": "Product cannot be changed during PATCH request."
}
```

**<u>Status:</u>** ```400 Bad Request```

If we try to send request with ```more``` quantity than ```available in the stock```, we get
the following response:

```
{
    "statusCode": 400,
    "status": "Bad Request",
    "message": "error",
    "errors": [],
    "description": "Not enough stock available."
}
```
**<u>Status:</u>** ```400 Bad Request```


**<u>Authentication Required:</u> YES**

If we are not authenticated, and try to perform ```PATCH``` operation, we get the 
following response.
```
{
    "statusCode": 401,
    "success": false,
    "message": "Full authentication is required to access this resource."
}
```

**<u>Status:</u> 401 Unauthorized**

***

> #### 4. DELETE
Removes an item from the cart

**Example**
<pre><b>DELETE</b> /api/cart_items/{cartItemId}</pre>

**Description**

```{cartItemId}``` is the id representing an individual cart item.
Like: ```/api/cart_items/1``` deletes cart item with ```id``` of ```1```.

**<u>Authentication Required</u>: YES**

User needs to be logged in to be able to delete the cart item.

* ```Authorization: Bearer <token>```
    * ```type```: ```Bearer```
    * ```<token>```: ```The actual token```

**<u>Working overview</u>**
- It requires user to be logged-in to be able to delete cart item.
- If user is deleting the ```last cart item```, then cart also gets deleted (removed) automatically.

**Response:**
```204 No Content```

***

# Activity Log

**<u>All the endpoints requires authentication.</u>**

Only the ```owner``` of the activity log can view it's log.
Apart from that, ```ADMIN``` user can view anyone's activity log.

> #### 1. GET
Fetch a single activity log.

<pre><b>GET</b> /api/activity_logs/{activityLogId}</pre>

**<u>Description:</u>**

```{activityLogId}``` refers to the id of an ```activityLog```.

If a ```logged-in``` user who is ```not an ADMIN``` tries to fetch own
activity log using ```GET``` endpoint, following response is returned.

<u>Note:</u> ```isMine``` field with ```true``` as it's value is returned.
```
{
  "id": 104,
  "owner": "/api/users/190",
  "activity": "PLACE ORDER",
  "description": "place order",
  "createdAt": "2025-03-05T22:54:35+00:00",
  "isMine": true
}
```

**<u>Status:</u> 200 OK**

If a ```logged-in``` user tries to fetch the other user's ( ```non-belonging user's``` ) activity log, 
then following response is returned
```
{
  "statusCode": 403,
  "success": false,
  "message": "Access Denied."
}
```

**<u>Status:</u> 403 Forbidden**

If ```ADMIN``` is ```logged-in ``` and the log belongs to the 
logged-in user (```ADMIN```), then following response is returned

<u>Note:</u> ```isLoggedInAdminLog```: ```true``` 
is an extra field returned in this case.
```
{
  "id": 103,
  "owner": "/api/users/189",
  "activity": "APPLY COUPON",
  "description": "apply coupon to cart",
  "createdAt": "2025-03-04T21:45:50+00:00",
  "isLoggedInAdminLog": true,
  "isMine": true
}
```

**<u>Status:</u> 200 OK**


If a user is ```not logged-in``` but tries to fetch activity log, then following
response is returned.

```
{
  "statusCode": 401,
  "success": false,
  "message": "Full authentication is required to access this resource."
}
```

**<u>Status:</u> 401 Unauthorized**


> #### 2. GET Collection
Fetches collection of activity logs.

<pre><b>GET</b> /api/activity_logs</pre>

```
[
  {
    "id": 117,
    "owner": "/api/users/189",
    "activity": "UPDATE CART ITEM",
    "description": "updated cart item ( Cart Item id: 51 )",
    "createdAt": "2025-03-06T07:02:49+00:00",
    "isLoggedInAdminLog": true,
    "isMine": true
  }
]
```

**<u>Status:</u> 200 OK**

If an ```ADMIN``` user is ```logged-in``` 
and the log belongs to the same ```ADMIN``` user, then we see
```isMine``` property that represents that the log belongs to the ```logged-in``` user (```ADMIN```), also
```isLoggedInAdminLog``` represents that the ```logged-in``` user is ```ADMIN``` and the log 
belongs to the  same user (```admin```).



<br />

If a ```logged-in``` user tries to send request to this route,
following response is returned with field ```isMine```: ```true```
indicating that the log belongs to the ```logged-in``` user.

```
[
  {
    "id": 155,
    "owner": "/api/users/190",
    "activity": "UPDATE CART ITEM",
    "description": "updated cart item ( Cart Item id: 59 )",
    "createdAt": "2025-03-13T03:04:52+00:00",
    "isMine": true
  },
  {
    "id": 154,
    "owner": "/api/users/190",
    "activity": "UPDATE CART ITEM",
    "description": "updated cart item ( Cart Item id: 59 )",
    "createdAt": "2025-03-13T02:45:22+00:00",
    "isMine": true
  }
]
```


**<u>Status:</u> 200 OK**

If user is ```not logged-in``` but tries to send the request,
then following response is returned.

```
{
  "statusCode": 401,
  "success": false,
  "message": "Full authentication is required to access this resource."
}
```

**<u>Status:</u> 401 Unauthorized**


****


# Coupon ( For `active` Cart )

Work with coupon in our system.

<b><u>Authentication Required:</u></b> YES

> #### PATCH

<pre><b>PATCH</b> /api/apply-discount</pre>

Applies/sets the coupon code to the currently ```active``` cart.
- Here, ```active cart``` refers to the ```current working cart``` in which
  user is interacting currently to add an item to the cart.


<b>Request body</b>
```
{
    "couponCode": "WINTER100"
}
```

<b><u>Header</u></b>
   - **Content-Type:** ```application/merge-patch+json```

<b><u>Authorization:</u></b>
Bearer ```<token>```

- Here, ```<token>``` refers to the ```API token``` that belongs to a user.

<b><u>Working Overview:</u></b>

If coupon is applied/set correctly into the cart, we get the following response:

```
{
    "discountType": "fixed",
    "discountValue": "100.00",
    "appliesTo": "{\"category\":79}",
    "status": "success",
    "message": "Coupon applied successfully."
}
```
This response represents various details regarding coupon like: which category does the coupon belongs to (`appliesTo`),
`discountValue` or `status`.

<br />

If there is no cart but still if we try to apply coupon, we get:

```
{
    "statusCode": 400,
    "status": "Bad Request",
    "message": "error",
    "errors": [
        "No cart found to apply the coupon."
    ],
    "description": "n/a"
}
```

**<u>Status:</u> 400 Bad Request**

<br />

If there is no cart and still if we try to apply coupon, we get:

```
{
    "statusCode": 400,
    "status": "Bad Request",
    "message": "error",
    "errors": [
        "No cart found to apply the coupon."
    ],
    "description": "n/a"
}
```
**<u>Status:</u> 400 Bad Request**


<br />

If we try to apply the coupon more than the allowed usage limit, then we get the 
following response:

```
{
    "statusCode": 422,
    "status": "Unprocessable Entity",
    "message": "error",
    "errors": [
        "You have already used this coupon the maximum allowed times."
    ],
    "description": "n/a"
}
```

**<u>Status:</u> 422 Unprocessable Entity**

<br /><br />

> #### PATCH

<pre><b>PATCH</b> /api/remove-coupon</pre>

Removes the coupon code from the current `active` cart.

**<u>Authenticated Required:</u> YES**
  - Authorization
    - type: Bearer

Following response is returned after coupon is removed:

```
{
    "message": "Coupon code removed successfully."
}
```

**<u>Status:</u> 200 OK**

If the coupon code is already NULL in the database, and still if we try
to remove it, we see the following response:

```
{
    "statusCode": 400,
    "status": "Bad Request",
    "message": "error",
    "errors": [
        "The coupon is already removed."
    ],
    "description": "n/a"
}
```

**<u>Status:</u> 400 Bad Request**

If we are not authenticated and still try to remove the coupon, we get the
following response:

```
{
    "statusCode": 401,
    "success": false,
    "message": "Full authentication is required to access this resource."
}
```

**<u>Status:</u> 401 Unauthorized**

*** 

# Coupon ( For `Pending` Order )

Coupon can be applied to the `Pending orders` to apply discount during their
`payment` for the purchase.

> #### POST

<pre><b>POST</b> /api/orders/{orderId}/apply-coupon</pre>

- `{orderId}` is the order id of the `pending order`.

<b><u>Authentication Required:</u> YES</b>

<b><u>Content-Type:</u> application/json</b>

JSON body `example` to send during the POST request:

```
{
    "couponCode": "SUMMER10"
}
```

The response:
```
{
    "discountType": "percentage",
    "discountValue": "10.00",
    "appliesTo": {
        "category": 78
    }
}
```

**<u>Status:</u>** 200 OK

<br />

> #### POST
<pre><b>POST</b> /api/orders/{orderId}/remove-coupon</pre>

Removes the coupon that is applied to the pending order.

**<u>Authentication Required:</u>** Yes

**<u>Working Overview</u>**

If no coupon is present (set) in the pending order, we get the following
response ( `because we tried to remove coupon that does not exist.` ):

```
{
    "statusCode": 422,
    "status": "Unprocessable Entity",
    "message": "error",
    "errors": [
        "No coupon code is present."
    ],
    "description": "n/a"
}
```
**<u>Status:</u>** 422 Unprocessable Entity

If the coupon has been applied (`previously`), then when we try to remove the coupon,
we get the following response (`on success`):

```
{
    "success": true,
    "description": "Coupon code SUMMER10 has been removed."
}
```

**<u>Status:</u>** 200 OK


***

# Coupon ( `ADMIN`: Manage Coupon )

**All the endpoints requires authentication**

  - `Authorization`: `Bearer <API_TOKEN>`
    - Here, `<API_TOKEN>` refers to the API token of an `ADMIN` user.

Manage coupon from the admin side.

> #### GET

<pre><b>GET</b> /api/coupons/{couponId}</pre>

- Here, `couponId` refers to id of a coupon.

**<u>Description:</u>**
Fetch a single coupon (`details`).

If `ADMIN` user is `logged-in` and tries to fetch the coupon, 
<pre>GET /api/coupons/1</pre>

following response is returned:

```
{
  "code": "SUMMER10",
  "discountType": "percentage",
  "discountValue": "10.00",
  "maxDiscountAmountForPercentage": "50.00",
  "appliesTo": {
    "category": 78
  },
  "startDate": "2024-12-25T08:40:12+00:00",
  "endDate": "2025-06-10T23:59:59+00:00",
  "usageLimit": 100,
  "singleUserLimit": 5,
  "description": "10% off for minimum order of Rs. 5000",
  "isActive": true
}
```
**<u>Status</u>** 200 OK

<br />

If a `non-admin` user tries to fetch a single coupon using the above endpoint,
following response is sent to the client:

```
{
  "statusCode": 403,
  "success": false,
  "message": "Access Denied."
}
```
**<u>Status:</u>** 403 Forbidden

<br />

> #### GET Collection

<pre><b>GET</b> /api/coupons</pre>

Retrieves a collection of coupons.

Example response:
```
[
  {
    "code": "SUMMER10",
    "discountType": "percentage",
    "discountValue": "10.00",
    "maxDiscountAmountForPercentage": "50.00",
    "appliesTo": {
      "category": 78
    },
    "startDate": "2024-12-25T08:40:12+00:00",
    "endDate": "2025-06-10T23:59:59+00:00",
    "usageLimit": 100,
    "singleUserLimit": 5,
    "description": "10% off for minimum order of Rs. 5000",
    "isActive": true
  },
  {
    "code": "WINTER100",
    "discountType": "fixed",
    "discountValue": "100.00",
    "appliesTo": {
      "category": 79
    },
    "startDate": "2024-12-25T08:40:12+00:00",
    "endDate": "2025-06-10T23:59:59+00:00",
    "usageLimit": 50,
    "singleUserLimit": 2,
    "description": "Rs. 100 off",
    "isActive": true
  }
]
```

> #### POST
<pre><b>POST</b> /api/coupons</pre>

Request data:
```
{
  "code": "DISCOUNT50",
  "discountType": "percentage",
  "discountValue": "50",
  "maxDiscountAmountForPercentage": "100",
  "appliesTo": {
    "category": 78
  },
  "minimumCartValue": "500",
  "startDate": "2025-01-01T00:00:00+00:00",
  "endDate": "2025-12-31T23:59:59+00:00",
  "usageLimit": 1000,
  "singleUserLimit": 5,
  "description": "Get 50% off on selected items up to $100 discount.",
  "isActive": true
}
```
After successful creation of the coupon, we get the response with
status `200 OK`.


If we try to create another coupon with the same `code`,
we get following response with `status`: `422 Unprocessable Entity`

```
{
    "success": false,
    "message": "code: The Coupon \"code\" must be unique.",
    "invalidKey": "code",
    "description": "Invalid data"
}
```

<br /><br />

> #### PATCH

<pre><b>PATCH</b> /api/coupons/{couponId}</pre>

Here, `couponId` refers to the unique id of a coupon.

**<u>Request body:</u>**

```
{
  "appliesTo": {
    "category": 79
  }
}
```

We're trying to change the category to something else, and in response, we get:
`200 OK` as the status code.


<br />

> #### DELETE

<pre><b>DELETE</b> /api/coupons/{couponId}</pre>
    
- Here, `couponId` refers to the unique id of a coupon.

If we send request to this endpoint, we get: `204 No Content`
in response which informs that the coupon has been deleted successfully.


# Inventory ( ADMIN Access )
This API Resource is restricted to the `admin` user.

All operations (`GET`, `GET Collection`, `POST`, `PATCH`, `DELETE`) are only accessible by the `admin`.

**<u>Authentication Required:</u> YES**
- Authorization: Bearer `<API_TOKEN>`
  - Here, `<API_TOKEN>` means the token of the user role `ADMIN`.

<br />

> #### GET
<pre><b>GET</b> /api/inventories/{inventoryId}</pre>
Fetch a single inventory by inventory id.

Example Response:
```
{
  "id": 1,
  "quantityInStock": 30,
  "quantitySold": 108,
  "product": "/api/products/91"
}
```
**<u>Status:</u>** 200 OK

<br />

> #### GET Collection
<pre><b>GET</b> /api/inventories</pre>

Fetches the inventories available in the system with status `200 OK`.

```
[
  {
    "id": 1,
    "quantityInStock": 30,
    "quantitySold": 108,
    "product": "/api/products/91"
  },
  {
    "id": 2,
    "quantityInStock": 98,
    "quantitySold": 3,
    "product": "/api/products/93"
  },
  {
    "id": 9,
    "quantityInStock": 100,
    "quantitySold": 10,
    "quantityBackOrdered": 10,
    "product": "/api/products/94"
  }
]
```

<br />

> #### POST
Store/save inventory into the system.

<pre><b>POST</b> /api/inventories</pre>

**<u>Request data:</u>**

```
{
    "quantityInStock": 10,
    "product": "/api/products/93"
}
```
- **<u>Note:</u>** Both `quantityInStock` and `product` fields are required.

**<u>Response:</u>**

```
{
    "id": 10,
    "quantityInStock": 10,
    "quantitySold": 0,
    "quantityBackOrdered": 0,
    "product": "/api/products/93"
}
```

**<u>Status:</u>** 200 OK

<br />

If we try to add the `same` (`existing`) `product` to the `inventory`, we get 
the following response:

```
{
    "success": false,
    "message": "product: This product is already in the inventory.",
    "invalidKey": "product",
    "description": "Invalid data"
}
```
**<u>Status:</u>** 422 Unprocessable Entity


<br />

> #### PATCH

<pre><b>PATCH</b> /api/inventories/{inventoryId}</pre>
  - Here, {inventoryId} refers to the unique inventory id.


Example:
`/api/inventories/11`

**<u>Request body:</u>**
```
{
    "quantityInStock": 20,
    "product": "/api/products/93"
}
```

**<u>Response body:</u>**
```
{
    "id": 11,
    "quantityInStock": 20,
    "quantitySold": 0,
    "quantityBackOrdered": 0,
    "product": "/api/products/93"
}
```
**<u>Status:</u> 200 OK**


> #### DELETE

Delete inventory from our system.

Example: 
  - <b>DELETE</b> `/api/inventories/11`

**<u>Response Status:</u>** 204 No Content


***


# Notification

Notification for the system users.

**<u>Note: All the endpoints requires authentication.</u>**

> #### GET
fetch a single notification that belongs to the `logged-in` user.

<pre><b>GET</b> /api/notifications/{notificationId}</pre>

  - here, `{notificationId}` refers to a unique id of a notification.

```
{
    "id": 5,
    "ownedBy": "/api/users/190",
    "message": "123 - Modified",
    "isRead": false
}
```

<br />

> #### GET Collection

Fetches a collection of Notification that belongs to the `logged-in` user,
and fetches all `notifications` for `admin` user.

<pre><b>GET</b> /api/notifications</pre>

<b><u>Response:</u></b>

`Status: 200 OK`
```
[
    {
        "id": 1,
        "ownedBy": "/api/users/190",
        "message": "Hello, your order has been placed.",
        "isRead": false
    },
    {
        "id": 3,
        "ownedBy": "/api/users/190",
        "message": "Your account is created successfully",
        "isRead": false
    },
    {
        "id": 5,
        "ownedBy": "/api/users/190",
        "message": "123 - Modified",
        "isRead": false
    }
]
```

<br />

> #### PATCH

Updates the notification `isRead` status (i.e. sets value to either `true` or `false`).

<pre><b>PATCH</b> /api/notifications/{notificationId}</pre>

  - Here, `{notificationId}` refers to the unique id of a notification.

If `owner` of the notification tries to update the isRead status of the 
notification, we get status of `200 OK` in response.

<b><u>JSON Body:</u></b>
```
{
    "isRead": false
}
```

<br />

If a `non-owner` user of the notification tries to perform a `PATCH` operation to change
`isRead` status, then `403 Forbidden` status is returned.

<b><u>JSON Body:</u></b>
```
{
    "isRead": true
}
```

```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```

<br />

> #### POST (ADMIN only)

Creates notification (for users).

<pre><b>POST</b> /api/notifications</pre>

**<u>Request body:</u>**

```
{
    "message": "Hi there, new test notification, please ignore it.",
    "ownedBy": "/api/users/190"
}
```

**<u>Response:</u>**

```
{
    "id": 1,
    "ownedBy": "/api/users/190",
    "message": "Hi there, new test notification, please ignore it.",
    "isRead": false
}
```

<br />

> #### DELETE

Deletes a notification from the system.

<pre><b>DELETE</b> /api/notifications/{notificationId}</pre>

- Here, `{notificationId}` refers to a unique notification id.

`204 No Content` status is returned when deletion is successful.

<br />
If a non-owner user tries to delete other user's notification,
`403 Forbidden` status is returned.

```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```

***

# Order

Order specific operations.

**<u>Authentication Required:</u> YES**

All the endpoints requires authentication.

> #### GET

Retrieves a single Order.

<pre><b>GET</b> /api/orders/{orderId}</pre>
  - Here, `{orderId}` refers to a unique id of an order.

If we're the `owner` of the order, it returns our order. (If `logged-in` `user` is the `owner` of the order)
Example response:

`Status: 200 OK`
```
{
    "id": 21,
    "ownedBy": "/api/users/189",
    "status": "order_placed",
    "totalPrice": "240.00",
    "couponCode": "SUMMER10",
    "shippingAddress": "/api/shipping_addresses/1",
    "paymentStatus": "paid",
    "currency": "usd",
    "shippingStatus": "not_initiated",
    "shippingMethod": "/api/shipping_methods/1",
    "createdAt": "2025-02-15T19:04:26+00:00",
    "updatedAt": "2025-02-15T19:05:43+00:00"
}
```

If the order `does not belong to us` and we're not `admin`, then
we get `403 Forbidden` in response
```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```


<br />

> #### GET Collection

Retrieves all the orders of a `logged-in` `user` (or `admin` can veiw everyone's order).

`Status: 200 OK`

```
[
    {
        "id": 19,
        "ownedBy": "/api/users/190",
        "status": "order_placed",
        "totalPrice": "180.00",
        "couponCode": "SUMMER10",
        "shippingAddress": "/api/shipping_addresses/1",
        "paymentStatus": "paid",
        "currency": "usd",
        "shippingStatus": "not_initiated",
        "shippingMethod": "/api/shipping_methods/1",
        "createdAt": "2025-02-14T21:10:45+00:00",
        "updatedAt": "2025-02-14T22:27:52+00:00"
    },
    {
        "id": 22,
        "ownedBy": "/api/users/190",
        "status": "order_placed",
        "totalPrice": "40.00",
        "shippingAddress": "/api/shipping_addresses/5",
        "paymentStatus": "paid",
        "currency": "usd",
        "shippingStatus": "not_initiated",
        "shippingMethod": "/api/shipping_methods/1",
        "createdAt": "2025-02-28T20:59:14+00:00",
        "updatedAt": "2025-02-28T23:41:39+00:00"
    }
]
```

<br />

> #### POST

Create order ( transfers data of cart to order table. )

<pre><b>POST</b> /api/orders</pre>

**<u>JSON sent during request to place order:</u>**

```
{
    "shippingMethod": "/api/shipping_methods/2",
    "shippingAddress": "/api/shipping_addresses/5"
}
```

`Response` <u>Status:</u> 201 Created
```
{
    "status": "Order Placed successfully. Please make payment to confirm your order."
}
```

We need to send `shippingMethod` along with `shippingAddress`
in the request to place the order.

If we send the wrong `shipping address`, we get `400 Bad Request` in response.

`JSON body:`
```
{
    "shippingMethod": "/api/shipping_methods/2",
    "shippingAddress": "/api/shipping_addresses/1"
}
```

`Response:`
<u>Status:</u> 400 Bad Request
```
{
    "status": 400,
    "error": "Bad Request",
    "message": "Item not found for \"/api/shipping_addresses/1\"."
}
```

*** 

# Payment

Payment information retrieval and stripe session id generation (`for frontend to process payment`).

**<u>\* All the endpoints requires authentication</u>**

> #### GET
<pre><b>GET</b> /api/payments/{paymentId}</pre>
If owner ( or `ADMIN` user ) tries to fetch own payment information, then following response with status `200 OK` is returned.

```
{
    "id": 43,
    "order": "/api/orders/18",
    "paymentMethod": "card",
    "paymentStatus": "paid",
    "amount": "11200.00",
    "paymentDate": "2025-02-13T22:20:00+00:00",
    "transactionId": "ch_3QsAlDJ8cBprh1gR1yzfwHLu",
    "billingAddress": "[ Address Line 1: 123 Main St.], [ Address Line 2: 222 Temple Road ]",
    "lineItems": [
        {
            "quantity": 2,
            "price_data": {
                "currency": "usd",
                "unit_amount": 3600,
                "product_data": {
                    "name": "Dairy Milk"
                }
            }
        },
        {
            "quantity": 2,
            "price_data": {
                "currency": "usd",
                "unit_amount": 2000,
                "product_data": {
                    "name": "Table Fan"
                }
            }
        }
    ],
    "stripeSessionId": "cs_test_b1YhUY8I5kC1kZIofIweWjnJD4jsKZNCaKCasAc1Du5XNGdyeKqgzBJQ7H"
}
```


If non-owner of the payment tries to fetch payment using `paymentId`, then
`403 Forbidden` response is returned.

```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```

<br />

> #### GET Collection
<pre><b>GET</b> /api/payments</pre>

Fetches collection of payment information.

If a logged-in user is the owner of the payment (information), then
all the related information is returned with `200 OK` status.

**<u>JSON Response:</u>**

```
[
    {
        "id": 43,
        "order": "/api/orders/18",
        "paymentMethod": "card",
        "paymentStatus": "paid",
        "amount": "11200.00",
        "paymentDate": "2025-02-13T22:20:00+00:00",
        "transactionId": "ch_3QsAlDJ8cBprh1gR1yzfwHLu",
        "billingAddress": "[ Address Line 1: 123 Main St.], [ Address Line 2: 222 Temple Road ]",
        "lineItems": [
            {
                "quantity": 2,
                "price_data": {
                    "currency": "usd",
                    "unit_amount": 3600,
                    "product_data": {
                        "name": "Dairy Milk"
                    }
                }
            },
            {
                "quantity": 2,
                "price_data": {
                    "currency": "usd",
                    "unit_amount": 2000,
                    "product_data": {
                        "name": "Table Fan"
                    }
                }
            }
        ],
        "stripeSessionId": "cs_test_b1YhUY8I5kC1kZIofIweWjnJD4jsKZNCaKCasAc1Du5XNGdyeKqgzBJQ7H"
    },
    {
        "id": 45,
        "order": "/api/orders/20",
        "paymentMethod": "card",
        "paymentStatus": "paid",
        "amount": "112000.00",
        "paymentDate": "2025-02-15T18:09:09+00:00",
        "transactionId": "ch_3QspnYJ8cBprh1gR0k1Jp7zM",
        "billingAddress": "[ Address Line 1: 123 Main St.], [ Address Line 2: 222 Temple Road ]",
        "lineItems": [
            {
                "quantity": 28,
                "price_data": {
                    "currency": "usd",
                    "unit_amount": 4000,
                    "product_data": {
                        "name": "Dairy Milk"
                    }
                }
            }
        ],
        "stripeSessionId": "cs_test_a1LAyONCuafOqqin6xUedk4o3VFpX5LZKZvcliDQxLkNAqJg0yhsHZ9ycL"
    },
    {
        "id": 46,
        "order": "/api/orders/21",
        "paymentMethod": "card",
        "paymentStatus": "paid",
        "amount": "21600.00",
        "paymentDate": "2025-02-15T19:05:43+00:00",
        "transactionId": "ch_3QsqgJJ8cBprh1gR0dI0RYsa",
        "billingAddress": "[ Address Line 1: 123 Main St.], [ Address Line 2: 222 Temple Road ]",
        "lineItems": [
            {
                "quantity": 6,
                "price_data": {
                    "currency": "usd",
                    "unit_amount": 3600,
                    "product_data": {
                        "name": "Dairy Milk"
                    }
                }
            }
        ],
        "stripeSessionId": "cs_test_a15BhTA9AQJZD5ivfXrOihhRTSnzOzBTOKv3JWh9ugtK8PLkH6dX4OfFcp"
    }
]
```

<br />

> #### POST
<pre><b>POST</b> /api/payments</pre>

**<u>Content-Type</u>** application/json

**<u>Request body:</u>**
```
{
    "order": "/api/orders/{orderId}"
}
```
- Here, `{orderId}` refers to the `orderId` that we want to make payment of ( Note: We get `stripe session id` in the response and that is utilized by the frontend to `process payment` / `make payment` ).

If `owner` of the `order` tries to send POST request to this endpoint, then stripe session id is returned 
with `201 Created` status code.

```
{
    "stripeSessionId": "cs_test_a1cT9OBtSVle8P9CmtrA87iPfF7PMEh9pf3SUSWxqOtcCdqofWV912KM3G"
}
```

If a non-owner (other than `ADMIN` user) tries to send `POST` request, then
`403 Forbidden` is returned.

```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```

*** 

# Product

Product-specific API resource.

> #### GET
<pre><b>GET</b> /api/products/{productId}</pre>

- Here, `{productId}` refers to the product id.

Retrieves a single product with `200 OK` as status.

```
{
    "id": 91,
    "name": "Dairy Milk",
    "description": "Changed dary milk description",
    "price": "40.00",
    "category": "/api/categories/79",
    "isActive": true,
    "productImage": "<URL_OF_IMAGE_FILE>"
}
```

**\* Here, I've mentioned that <URL_OF_IMAGE_FILE> which means that our API responds with image URL in productImage field.**

<br />

> #### GET Collection
<pre><b>GET</b> /api/products</pre>

Retrieves a collection of product with `200 OK` as status.

```
[
    {
        "id": 91,
        "name": "Dairy Milk",
        "description": "Changed dary milk description",
        "price": "40.00",
        "category": "/api/categories/79",
        "isActive": true,
        "productImage": "<URL_OF_IMAGE_FILE>"
    },
    {
        "id": 93,
        "name": "Table Fan",
        "description": "This is a mini table fan",
        "price": "20.00",
        "category": "/api/categories/78",
        "isActive": true,
        "productImage": "<URL_OF_IMAGE_FILE>"
    },
    {
        "id": 94,
        "name": "Chocolate Bar",
        "description": "Delicious chocolate bar",
        "price": "30.00",
        "category": "/api/categories/79",
        "isActive": true,
        "productImage": "<URL_OF_IMAGE_FILE>"
    }
]
```

<br />

> #### POST

<pre><b>POST</b> /api/products</pre>

Creates a product.

when we send product info, and we're logged-in as `ADMIN`, then `200 OK` status is returned.

If an `admin` user tries to send a `POST` request in `form-data` with following
data, then `201 Created` response is returned.
```
    name        -> "Dairy Milk"
    description -> "The Dairy Milk Silk chocolate"
    price       -> 20.00
    category    -> 79
    isActive    -> 1
    file        -> <PICK_FILE>
```
Here, `isActive` value `1` refers to `true` or it is active, so that
client can `render` `active product` in the product listings in the `frontend` side.

Response body is returned as:
```
{
    "id": 97,
    "name": "Dairy Milk",
    "description": "The Dairy Milk Silk chocolate",
    "price": "20.00",
    "category": "/api/categories/79",
    "isActive": true,
    "productImage": "<AWS_FILE_URL_IS_CONSTRUCTED_AND_RETURNED_HERE>"
}
```

<br />


If a `non-admin` user tries to create product, `403 Forbidden` status is returned.

```
{
    "statusCode": 403,
    "status": "Forbidden",
    "message": "error",
    "errors": [],
    "description": "n/a"
}
```

<br />

> #### PATCH

Updates product.

<pre><b>PATCH</b> /api/products/{productId}</pre>

  - Here, `{productId}` refers to a unique product id that we want to update.

Example:
`/api/products/98`

**<u>JSON Body:</u>**
```
{
    "name": "Dairy Milk - Updated",
    "price": "40.00",
    "description": "updated one",
    "category": "/api/categories/78",
    "isActive": true
}
```

**<u>Response:</u>**
```
{
    "id": 98,
    "name": "Dairy Milk - Updated",
    "description": "updated one",
    "price": "40.00",
    "category": "/api/categories/78",
    "updatedAt": "2025-03-23T12:49:45+00:00",
    "isActive": true,
    "productImage": "<AWS_IMAGE_URL>"
}
```

If a `non-admin` user or the user that does not have the 
`ROLE_PRODUCT_EDIT` role, then we get `403 Forbidden` response.

<br />

> #### DELETE

Deletes product using product id.

<pre><b>DELETE</b> /api/products/{productId}</pre>

- Here, `{productId}` refers to the individual product id.


When user with roles `ROLE_ADMIN` or `ROLE_PRODUCT_DELETE` tries to delete 
an existing product, the product gets deleted from both the AWS, and the database information is removed too
with status of `204 No Content` returned to us.

*** 

# ProductImage ( Update Product Image )

> #### POST

<pre><b>POST</b> /api/products/{productId}/image</pre>
Updates image of the product using product id.

Example: `/api/products/94/image`

We are `not sending data in raw json`, but we send `file` as `form-data`.
The file gets updated in the AWS S3 Storage (cloud) i.e. it gets replaced by removing previous image file, 
and info (like: `originalFileName`) gets updated in the database.

`form-data` is as follows
```
  file - <SELECT_FILE>
```

here, file is the key, and value ( <SELECT_FILE> ) is the file that we attach to the file key.

After successful update, we get `200 OK` in response.

Following response is returned.

```
{
    "id": "94",
    "name": "Chocolate Bar",
    "description": "Delicious chocolate bar",
    "price": "30.00",
    "category": "/api/categories/79",
    "isActive": true,
    "productImage": "<AWS_FILE_URL>"
}
```

*** 

# ProductCategory (Category)

All the operations ( GET, GET Collection, POST, PATCH, DELETE) requires
authentication (`logged-in as ADMIN user`).

> #### GET
<pre><b>GET</b> /api/categories/{categoryId}</pre>

- Here, `{categoryId}` refers to the product category id.

Example: `/api/categories/79`

**<u>Response:</u>**

Status: `200 OK`
```
{
    "id": 79,
    "categoryName": "Gifts",
    "description": "Birthday Gifts"
}
```

<br />

> #### GET Collection
<pre><b>GET</b> /api/categories</pre>

Retrieves a collection of product categories.

**Status:** `200 OK`

**JSON Response:**

```
[
    {
        "id": 78,
        "categoryName": "Electronics",
        "description": "An electronics items including heaters, fans, or electric equipment related items"
    },
    {
        "id": 79,
        "categoryName": "Gifts",
        "description": "Birthdy Gifts"
    }
]
```

<br />

> #### POST
<pre><b>POST</b> /api/categories</pre>

Saves `product category` into the system.

**<u>JSON Body:</u>**

`Content-Type`: `application/json`

```
{
    "categoryName": "Groceries",
    "description": "Groceries products"
}
```

**<u>Response:</u>**

Status: `201 Created`
```
{
    "id": 86,
    "categoryName": "Groceries",
    "description": "Groceries products"
}
```


<br />

> #### PATCH
<pre><b>PATCH</b> /api/categories/{categoryId}</pre>

Updates product category.

Example: `/api/categories/86`

Request `JSON` body:
```
{
    "categoryName": "Groceries - updated",
    "description": "Groceries products - updated"
}
```

**<u>JSON Response:</u>**

**Status:** `200 OK`
```
{
    "id": 86,
    "categoryName": "Groceries - updated",
    "description": "Groceries products - updated"
}
```

<br />

> #### DELETE
<pre><b>DELETE</b> /api/categories/{categoryId}</pre>

Deletes/Removes category from the database.

**<u>Example:</u>** `DELETE` `/api/categories/86`

**<u>Response:</u>** `204 No Content`


***

# ProductReview

Review of a product.

> #### GET
<pre><b>GET</b> /api/product_reviews/{productReviewId}</pre>

- Here, `{productReviewId}` refers to a unique id of a product review.

**<u>Example:</u>** `GET` `/api/product_reviews/{productReviewId}`

**<u>Status:</u>** `200 OK`
```
{
    "id": 1,
    "product": "/api/products/91",
    "owner": "/api/users/189",
    "rating": 4,
    "reviewText": "Great product! Highly recommended.",
    "isActive": false,
    "dateCreated": "1 month ago"
}
```

<br />

If `isActive` is `false` and we try to fetch that specific review 
( and we're not `logged-in` as the `owner` of the review ), we get
`403 Forbidden` in response.

```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```

<br />

> #### GET Collection
Retrieves a collection of reviews (active reviews).

For `ADMIN` user, fetches all `active`, or `non-active` reviews.

For a `non-admin` user, and `non-owner` of the review, it lists only
`active` reviews.

For `owner`, it retrieves a collection of both `active` and `non-active` reviews.


**<u>Example:</u>** `/api/product_reviews`

**<u>Response:</u>**

**<u>Status:</u>** `200 OK`

```
[
    {
        "id": 112,
        "product": "/api/products/93",
        "owner": "/api/users/190",
        "rating": 5,
        "reviewText": "Awesome product, I would recommend this product to everyone",
        "isActive": true,
        "dateCreated": "1 hour ago"
    },
    {
        "id": 113,
        "product": "/api/products/91",
        "owner": "/api/users/191",
        "rating": 5,
        "reviewText": "Nice product, will tell friends to buy too.",
        "isActive": true,
        "dateCreated": "44 minutes ago"
    }
]
```

<br />

> #### POST
<pre><b>POST</b> /api/product_reviews</pre>

Product needs to be marked as `shipped` in the `order` 
so that review can be given to `ordered` products.

**<u>JSON body during request:</u>**

**<u>Content-Type:</u>** `application/json`

```
{
    "product": "/api/products/93",
    "rating": 5,
    "reviewText": "Awesome product, I would recommend this product to everyone"
}
```

**<u>JSON Response:</u>**

**<u>Status:</u>** `201 Created`
```
{
    "id": 112,
    "product": "/api/products/93",
    "owner": "/api/users/190",
    "rating": 5,
    "reviewText": "Awesome product, I would recommend this product to everyone",
    "isActive": false
}
```

<br />

> #### PATCH
<pre><b>PATCH</b> /api/product_reviews/{productReviewId}</pre>

**<u>Request JSON:</u>**
```
{
    "rating": 4,
    "reviewText": "Awesome product - updated"
}
```

**<u>Response:</u>**

**<u>Status:</u>** `200 OK`

```
{
    "id": 112,
    "product": "/api/products/93",
    "owner": "/api/users/190",
    "rating": 4,
    "reviewText": "Awesome product - updated",
    "isActive": false,
    "dateCreated": "4 hours ago",
    "dateUpdated": "1 minute ago"
}
```

**<u>Note:</u>** Owner of the review can perform PATCH operation.

- We cannot `change`/`modify` product during update. If we do so, we get validation error.
- Non-owner cannot perform update, if we do so, we get error too `403 Forbidden`
  - `403 Forbidden`, if product `isActive`: `true`
  - `404 Not Found` if product is set to `isActive`: `false` (`inactive`),
    because we're trying to hide product if it's `inactive`, and we want to
    prevent modification by a `non-owner` user.
- Owner can perform update no matter `isActive` is set to `true`/`false`.

- After update of data, the `isActive` is set to `false` automatically (`internally`)
  so that admin can again approve it.


<br />

> #### DELETE
<pre><b>DELETE</b> /api/product_reviews/{productReviewId}</pre>

**<u>Example:</u>** `DELETE` `/api/product_reviews/112`

Removes the product review from the database.

**<u>Response:</u>** `204 No Content`


***

# ShippingAddress

> #### GET
<pre><b>GET</b> /api/shipping_addresses/{shippingAddressId}</pre>

- Here, `{shippingAddressId}` refers to the shipping address unique id.

If `owner` of the user tries to access own shipping address, then following response is returned.

`Status`: `200 OK`
```
{
    "owner": "/api/users/190",
    "addressLine1": "The <street name>",
    "city": "<city name>",
    "state": "<STATE>",
    "postalCode": "<POSTAL_CODE>",
    "country": "<COUNTRY>",
    "phoneNumber": "<PHONE_NUMBER>"
}
```

<br />

If a `non-owner` user (who is also not an `ADMIN`) is `logged-in` and tries to access other user's 
shippingAddress, then `403 Forbidden` is returned.

```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```

**<u>Note:</u>** <i>**<u>Admin user can access anyone's shipping address.</u>**</i>

<br />

> #### GET Collection

Retrieves a collection of shipping addresses.

If a `Logged-in` user is also the `owner`, then a list of `shipping addresses` that are
available in the database related to the `owner` is returned.

**<u>Response:</u>**
```
[
    {
        "owner": "/api/users/190",
        "addressLine1": "The <street name>",
        "city": "<city name>",
        "state": "<STATE>",
        "postalCode": "<POSTAL_CODE>",
        "country": "<COUNTRY>",
        "phoneNumber": "<PHONE_NUMBER>"
    }
]
```

<br />

> #### POST
<pre><b>POST</b> /api/shipping_addresses</pre>

Saves shipping address of a `logged-in` user to the database.

**<u>Example:</u>** `/api/shipping_addresses`

**<u>JSON Body:</u>**
```
{
    "addressLine1": "The Mountain/Hilly Road",
    "addressLine2": "Lane 1, House No. 1",
    "city": "Kathmandu",
    "state": "State 3 (Bagmati)",
    "postalCode": "44600",
    "country": "Nepal",
    "phoneNumber": "0123456789"
}
```

**<u>Response:</u>**

`Status`: `201 Created`

```
{
    "owner": "/api/users/190",
    "addressLine1": "The Mountain/Hilly Road",
    "addressLine2": "Lane 1, House No. 1",
    "city": "Kathmandu",
    "state": "State 3 (Bagmati)",
    "postalCode": "44600",
    "country": "Nepal",
    "phoneNumber": "0123456789"
}
```

**<u>Note:</u>** One user can have max 3 shipping addresses, if the user tries to
add more shipping address than maximum allowed (i.e. 3), then `422 Unprocessable Entity` is returned.

```
{
    "success": false,
    "message": "Too many shippingAddresses",
    "description": "Please make sure to edit the existing one. Max shipping address reached."
}
```

<br />

> #### PATCH

<pre><b>PATCH</b> /api/shipping_addresses/{shippingAddressId}</pre>

If an `owner` (or `admin`) tries to update the shipping address using following json,
`200 OK` in response is returned.

**<u>Content-Type</u>**: `application/merge-patch+json`

**<u>Request JSON</u>**
```
{
    "addressLine1": "The Mountain/Hilly Road - updated",
    "addressLine2": "Lane 1 - Updated"
}
```

**<u>Response JSON</u>**

**<u>Status:</u>** `200 OK`

```
{
    "owner": "/api/users/190",
    "addressLine1": "The Mountain/Hilly Road - updated",
    "addressLine2": "Lane 1 - Updated",
    "city": "Kathmandu",
    "state": "State 3 (Bagmati)",
    "postalCode": "44600",
    "country": "Nepal",
    "phoneNumber": "0123456789"
}
```

<br />

If a `non-owner` ( who is `non-admin` ) tries to perform an update, then `403 Forbidden` response is
returned.

```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```

<br />

> #### DELETE
<pre><b>DELETE</b> /api/shipping_addresses/{shippingAddressId}</pre>

If an owner (or `admin`) tries to delete shipping address,
then `204 No Content` status is returned with empty response body.

If a non-owner (who is not an admin) tries to delete, then `403 Forbidden` is
returned.
```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```

***

# ShippingMethod

> #### GET
<pre><b>GET</b> /api/shipping_methods/{shippingMethodId}</pre>

  - Here, `{shippingMethodId}` refers to the unique shipping method id.

Fetches shipping method present in the database.

`Example`: `/api/shipping_methods/1`

**<u>Response:</u>**

`Status`: `200 OK`

```
{
    "id": 1,
    "name": "Standard Shipping",
    "cost": "10.00",
    "estimatedDeliveryTime": "10 days"
}
```

<br />

> #### GET Collection
<pre><b>GET</b> /api/shipping_methods</pre>

Retrieves a collection of shipping methods.

```
[
    {
        "id": 1,
        "name": "Standard Shipping",
        "cost": "10.00",
        "estimatedDeliveryTime": "10 days"
    },
    {
        "id": 2,
        "name": "Express Shipping",
        "cost": "20.00",
        "estimatedDeliveryTime": "5 days"
    }
]
```

<br />

> #### POST
<pre><b>POST</b> /api/shipping_methods</pre>

Saves shipping method to the database.

**<u>Authentication Required:</u>** YES ( `ADMIN` can only post to this route )

**<u>Request Body:</u>**
```
{
    "name": "Eco Friendly Shipping",
    "cost": "100",
    "estimatedDeliveryTime": "15 days"
}
```


**<u>JSON Response:</u>**

`Status`: `200 OK`
```
{
    "id": 3,
    "name": "Eco Friendly Shipping",
    "cost": "100",
    "estimatedDeliveryTime": "15 days"
}
```

<br />

> #### PATCH
<pre><b>PATCH</b> /api/shipping_methods/{shippingMethodId}</pre>

- Here, `{shippingMethodId}` refers to the unique shipping method id.

\* **<u>Note:</u>** `Admin` user can only Perform PATCH operation.

**<u>JSON sent during request:</u>**

`Content-Type`: `application/merge-patch+json`

```
{
    "name": "Eco Friendly Shipping - Updated",
    "cost": "100",
    "estimatedDeliveryTime": "15 days"
}
```

**<u>Response:</u>**

`Status`: `200 OK`

```
{
    "id": 17,
    "name": "Eco Friendly Shipping - Updated",
    "cost": "100",
    "estimatedDeliveryTime": "15 days"
}
```

<br />

> #### DELETE
<pre><b>DELETE</b> /api/shipping_methods/{shippingMethodId}</pre>

Deletes/Removes shipping method from the database.

Example: `DELETE`: `/api/shipping_methods/17`

`204 No Content` is returned after deletion is successful.


***

# User

> GET
<pre><b>GET</b> /api/users/{userId}</pre>

Retrieves a single user information.

Example: `GET` `/api/users/189`

`Status`: `200 OK`

```
{
    "id": 189,
    "email": "test@gmail.com",
    "username": "test.test",
    "firstName": "test",
    "lastName": "test",
    "isActive": false,
    "isVerified": false
}
```

If a non-owner tries to access another user's information, we see
`403 Forbidden` in response.

```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```

<br />

> #### GET Collection
<pre><b>GET</b> /api/users</pre>

When `ADMIN` user tries to fetch a collection of users, then 
following response is returned.

**<u>Response:</u>**

`Status`: `200 OK`

```
[
    {
        "id": 190,
        "email": "jakubowski.adrian@dickinson.com",
        "username": "bsipes",
        "firstName": "Jorge",
        "lastName": "Turner",
        "isActive": false,
        "isVerified": false
    },
    {
        "id": 191,
        "email": "kautzer.georgianna@gmail.com",
        "username": "iva.doyle",
        "firstName": "Carroll",
        "lastName": "Considine",
        "isActive": false,
        "isVerified": false
    }
]
```

<br />

> #### POST
<pre><b>POST</b> /api/users</pre>

Creates a user.

<i>* Note: `userName` and `email` must be unique.</i>

**<u>JSON Body:</u>**

`Content-Type`: `application/json`

```
{
    "email": "coder@gmail.com",
    "password": "123",
    "userName": "coder_user",
    "firstName": "coder",
    "lastName": "boy"
}
```

**<u>Response:</u>**

```
{
    "id": 200,
    "email": "coder@gmail.com",
    "username": "coder_user",
    "firstName": "coder",
    "lastName": "boy",
    "isActive": false,
    "isVerified": false
}
```

**<u>Working Mechanism:</u>**

- `Non-admin` user cannot set `custom` roles when creating user even if user 
sends the roles, it will be ignored.

- `Admin` user can set custom roles when creating user.

- `userName` and `email` must be unique. Trying to create user with existing
  `userName` or `email` will return validation error response.

`Example of ADMIN user creating user`
```
{
    "email": "programming_user@gmail.com",
    "password": "123",
    "userName": "programming_user",
    "firstName": "programming",
    "lastName": "user",
    "roles": ["ROLE_USER_EDIT", "ROLE_USER_DELETE"]
}
```

**<u>Response:</u>**
```
{
    "id": 215,
    "email": "programming_user@gmail.com",
    "username": "programming_user",
    "firstName": "programming",
    "lastName": "user",
    "roles": [
        "ROLE_USER_EDIT",
        "ROLE_USER_DELETE"
    ],
    "isActive": false,
    "isVerified": false
}
```

<br />

> #### PATCH
<pre><b>PATCH</b> /api/users/{userId}</pre>

Updates a user.

**<u>Content-Type:</u>** `application/merge-patch+json`

**<u>Request JSON:</u>**
```
{
    "firstName": "firstnameupdated",
    "roles": ["ROLE_USER_EDIT", "ROLE_USER_DELETE"]
}
```

**<u>Response:</u>**

`Status`: `200 OK`

```
{
    "id": 208,
    "email": "computeruser@gmail.com",
    "username": "computer_user",
    "firstName": "firstnameupdated",
    "lastName": "user",
    "roles": [
        "ROLE_USER_EDIT",
        "ROLE_USER_DELETE"
    ],
    "isActive": false,
    "isVerified": false
}
```


**<u>Working Mechanism:</u>**
- Admin can assign roles during UPDATE to any user.
- Even owner cannot set/assign roles during update.


<br />

> #### DELETE
<pre><b>DELETE</b> /api/users/{userId}</pre>

Instead of removing user form database, it sets
`isActive`: `false` into the database.

**<u>Response:</u>** `204 No Content`


***

# Wishlist

> #### GET
<pre><b>GET</b> /api/wishlists/{wishlistId}</pre>
  - Here, `{wishlistId}` refers to a single wishlist unique identifier.

Fetches a single wishlist.

**<u>Response:</u>**

`Example`: `/api/wishlists/20`

```
{
    "id": 20,
    "product": "/api/products/91"
}
```

If a non-owner (or who is also not `admin`), `403 Forbidden` is
returned.

`Status`: `403 Forbidden`

```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```

<br />

> #### GET Collection
<pre><b>GET</b> /api/wishlists</pre>

Retrieves a collection of wishlist.

- Admin can access anyone's wishlist 
- Owner of the wishlist sees own wishlist item(s) only.

**<u>Response:</u>**

`Status`: `200 OK`

```
[
    {
        "id": 20,
        "product": "/api/products/91"
    },
    {
        "id": 21,
        "product": "/api/products/93"
    }
]
```


<br />

> #### POST
<pre><b>POST</b> /api/wishlists</pre>

**<u>Request Data:</u>**

`Content-Type`: `application/json`

```
{
    "product": "/api/products/93"
}
```

**<u>Response:</u>**

`Status`: `201 Created`

```
{
    "id": 21,
    "product": "/api/products/93"
}
```

**<u>Working Overview:</u>**
- User cannot store same product twice in the wishlist.


<br />

> #### DELETE
<pre><b>DELETE</b> /api/wishlists/{wishlistId}</pre>

Removes wishlist from the database.

If a non-owner (who is also not an `admin`) tries to DELETE wishlist item,
then `403 Forbidden` is returned.

```
{
    "statusCode": 403,
    "success": false,
    "message": "Access Denied."
}
```


If owner or admin tries to delete wishlist item, `status` of `204 No Content` is returned,
and the item gets deleted from the database.

