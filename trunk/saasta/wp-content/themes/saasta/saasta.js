
/* Saasta Javascript */

function addLoadEvent(func) 
{
    var oldonload = window.onload;
    if (typeof window.onload != 'function') 
    {
        window.onload = func;
    } else 
    {
        window.onload = 
            function() 
            {
                if (oldonload) {
                    oldonload();
                }
                func();
            }
    }
}

function findProductElems(base,elemName)
{
    var inputs = document.getElementsByTagName(elemName);
    var re = new RegExp("^"+base+"_([0-9]+)$");
    var r = new Array();

    for (var i = 0; i < inputs.length; i++)
    {
        var m = re.exec(inputs[i].name);
        if (m != null)
            r[m[1]] = inputs[i];
    }

    return r;
}

function computeOrderPrice(productSelects, unitPrices)
{
    var price = 0;

    for (var i = 0; i < productSelects.length; i++)
    {
        var sel = productSelects[i];
        var unitPrice = unitPrices[i];

        if (sel != null)
        {
            var o = sel.options[sel.selectedIndex];

            if (o != null && unitPrice != null)
            {
                price += o.value * unitPrice.value;
            } else
            {
                alert("JS error: no unit price found for product #"+i);
            }
        }
    }

    return price;
}

function updatePrice(sels, unitPrices)
{
    var priceElem = document.getElementById('order_price');
    var shippingCost = parseInt(document.getElementById('shipping_cost').value);
    // Compute order price from select elements:
    var orderPrice = computeOrderPrice(sels, unitPrices) + shippingCost;
    var d = Math.floor(orderPrice / 100);
    var f = Math.floor(Math.floor(orderPrice % 100));

    priceElem.innerHTML = d + '.' + (f < 10 ? '0'+f : f);
}

function registerPriceCompute()
{
    var productSelects = findProductElems("product", "select");
    var productUnitPrices = findProductElems("unit_price_product", "input");
    var priceUpdateFunc = 
        function() { updatePrice(productSelects, productUnitPrices); };

    for (var i = 0; i < productSelects.length; i++)
    {
        var sel = productSelects[i];

        // Register change handlers for all selectors.  When any of
        // the selectors change, price text gets automatically
        // updated.
        if (sel != null)
            sel.onchange = priceUpdateFunc;
    }

    priceUpdateFunc();
}

/**
 * Dynamically add an "add tags" textfield below post
 *
 * @param postId id of post, used to search for container (such as
 * div) with id taglist_postId
 */
function showAddTagForm(postId,button) {
    // hide button
    button.style.display="none";
    // ref points to the container
    var ref = document.getElementById("taglist_"+postId);
    if (ref) {
	var f = document.createElement("form");
	f.setAttribute("action", "saasta-addtags.php");
	f.setAttribute("method", "post");
	// post id
	f.appendChild(createInput("hidden","id",postId));
	// redirect_to
	var redirectURI = window.location.href;
	if ((idx = redirectURI.indexOf("#")) > -1)
	    redirectURI = redirectURI.substring(0, idx);
	redirectURI += "#saasta"+postId;
	f.appendChild(createInput("hidden","redirect_to",redirectURI));
	// text
	f.appendChild(document.createTextNode("add tags: "));
	// tag textfield
	var tf = createInput("text","tags","");
	tf.setAttribute("class","saastaui");
	tf.style.width = "300px";
	f.appendChild(tf);
	// some spacing
	f.appendChild(document.createTextNode(" "));
	// submit button
	var b = createInput("submit",null,"do it!");
	b.setAttribute("class","saastaui");
	f.appendChild(b);
	// add form to container
	ref.appendChild(f);
    }
}

/**
 * Create <input> node of specific type
 *
 * @param type type of input (text,submit,hidden...)
 * @param name name of input field, if not null
 * @param value value for input field
 */
function createInput(type,name,val) {
    var el = document.createElement("input");
    el.setAttribute("type",type);
    if (name != null) 
	el.setAttribute("name",name);
    el.setAttribute("value",val);
    return el;
}
