<form action="{{ route('form.submit') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Text -->
    <label for="text">Text:</label>
    <input type="text" id="text" name="text"><br><br>

    <!-- Password -->
    <label for="password">Password:</label>
    <input type="password" id="password" name="password"><br><br>

    <!-- Email -->
    <label for="email">Email:</label>
    <input type="email" id="email" name="email"><br><br>

    <!-- Number -->
    <label for="number">Number:</label>
    <input type="number" id="number" name="number"><br><br>

    <!-- Tel -->
    <label for="tel">Phone:</label>
    <input type="tel" id="tel" name="tel"><br><br>

    <!-- URL -->
    <label for="url">Website:</label>
    <input type="url" id="url" name="url"><br><br>

    <!-- Search -->
    <label for="search">Search:</label>
    <input type="search" id="search" name="search"><br><br>

    <!-- Date -->
    <label for="date">Date:</label>
    <input type="date" id="date" name="date"><br><br>

    <!-- Time -->
    <label for="time">Time:</label>
    <input type="time" id="time" name="time"><br><br>

    <!-- Datetime-local -->
    <label for="datetime">Date & Time:</label>
    <input type="datetime-local" id="datetime" name="datetime"><br><br>

    <!-- Month -->
    <label for="month">Month:</label>
    <input type="month" id="month" name="month"><br><br>

    <!-- Week -->
    <label for="week">Week:</label>
    <input type="week" id="week" name="week"><br><br>

    <!-- File -->
    <label for="file">Upload File:</label>
    <input type="file" id="file" name="file"><br><br>

    <!-- Checkbox -->
    <label for="checkbox">Accept Terms:</label>
    <input type="checkbox" id="checkbox" name="checkbox"><br><br>

    <!-- Radio -->
    <label>Choose One:</label><br>
    <input type="radio" id="option1" name="radio_option" value="option1">
    <label for="option1">Option 1</label><br>
    <input type="radio" id="option2" name="radio_option" value="option2">
    <label for="option2">Option 2</label><br><br>

    <!-- Range -->
    <label for="range">Range (1–100):</label>
    <input type="range" id="range" name="range" min="1" max="100"><br><br>

    <!-- Hidden -->
    <input type="hidden" name="hidden_field" value="hidden_value">

    <!-- Color -->
    <label for="color">Pick a Color:</label>
    <input type="color" id="color" name="color"><br><br>

    <!-- Image Button -->
    <label for="image_button">Image Submit:</label><br>
    <input type="image" src="/path-to-your-image.jpg" alt="Submit" width="100"><br><br>

    <!-- Submit -->
    <label for="submit">Submit Button:</label>
    <input type="submit" id="submit" value="Submit"><br><br>

    <!-- Reset -->
    <label for="reset">Reset Form:</label>
    <input type="reset" id="reset" value="Reset"><br><br>

  <!-- Multi-line textarea -->
    <label for="bio">Bio:</label>
    <textarea id="bio" name="bio" rows="5" cols="30"></textarea><br><br>

    <!-- Button -->
    <label for="button">Generic Button:</label>
    <input type="button" id="button" value="Click Me"><br><br>
</form>


// JSON

{
"model_name": "Product",
  "pagination_type": "backend",
  "generate_admin_interface": 1,
  "generate_api": 1,
  "generate_profile": 0,
  "columns": {
    "imageType": ["image*#", "cover#"], 
    "textType": ["title*#", "description*#"],
    "numberType": ["price*#", "stock#", "discount#"],
    "colorType": ["color#"],
    "dateType": ["sell_date#"],
    "timeType": ["sell_time#"],
    "yearType": ["sell_year#"],
    "booleanType": ["is_active*#", "is_popular#"],
    "selectType": [
      {
        "name": "use_status",
        "option_values": ["new", "used", "refurbished"],
        "option_labels": ["New", "Used", "Refurbished"]
      },
      {
        "name": "status",
        "option_values": ["active", "inactive", "draft"],
        "option_labels": ["Active", "Inactive", "Draft"]
      }
    ],
    "relationalType": [
      {
        "foreign_key": "product_category_id",
        "related_table": "product_categories",
        "related_table_id": "id",
        "screen_column_of_related_table": "title"
      }
    ],
    "fileType": ["invoice"],
    "textEditorType": ["about"],
    "TagType": ["tags"]
  },
  "seeder": [
    {
      "title": "Example Product",
      "description": "Demo description",
      "price": 150,
      "stock": 20,
      "discount": 10,
      "color": "#FF0000",
      "sell_date": "2025-07-01",
      "sell_time": "10:00:00",
      "sell_year": 2025,
      "is_active": 1,
      "is_popular": 0,
      "use_status": "new",
      "status": "active",
      "product_category_id": 1,
      "invoice": "uploads/invoice.pdf",
      "about": "<p>About this product...</p>",
      "tags": "tag1,tag2"
    }
  ]
}