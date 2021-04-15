(function ($) {
    $(".wc-multivendor-featured-datepicker").flatpickr({
        minDate: 'today'
    });



})(jQuery);

const { useState, useEffect, useRef } = React;
const { categories, products } = wcfeatured;

const main_category = categories.filter(cat => cat.parent == 0);

const get_sub_categories = (parent) => {
    return parent && parent > 0 ? categories.filter(cat => cat.parent == parent) : [];
}

const Categories = ({ name, category, onChange }) => {
    const on_update = (e) => {
        if (typeof onChange === 'function') {
            onChange(e.target.value)
        }
    }

    return (
        <select name={name} defaultValue={category} className="wcfm-select" onChange={on_update}>
            <option value="">Select a category</option>
            {main_category.map((cat) => {
                return <option className="level-0" value={cat.term_id}>{cat.name}</option>
            })}
        </select>
    )
}

const FeatureVendorAdd = (props) => {
    const [state, setState] = useState({
        start_date: new Date().toISOString().slice(0, 10),
        days: 1,
        category: ''
    });

    useEffect(() => {
        jQuery('.wc-multivendor-featured-datepicker').flatpickr({
            minDate: 'today'
        })
    }, []);

    const { start_date, days, category } = state;

    const onSubmit = (e) => {

        if (!start_date.length) {
            e.preventDefault();
            return alert('Start date is not valid');
        }

        if (!Number.isInteger(parseInt(days))) {
            e.preventDefault();
            return alert('Days is not valid');
        }

        if (!state.category) {
            e.preventDefault();
            return alert('Please select a category');
        }
    }

    return (
        <div className="wcfm-container" style={{ marginBottom: 40 }}>
            <div className="wcfm-content">
                <h2>Featured your store</h2>
                <div className="gap-10" />
                <form className="wcfm-vendor-featured-form wcfm-vendor-featured-store-form" method="POST">
                    <input type="hidden" name="_nonce_featured_vendor" value={props._nonce} />

                    <fieldset className="wcfm-vendor-featured-fieldset wcfm-vendor-featured-fieldset-grid">
                        <label>Start Date</label>
                        <input name="wcfm_featured_store_start_date" defaultValue={start_date} type="text" className="wcfm-text wc-multivendor-featured-datepicker" />

                        <label>Days</label>
                        <input name="wcfm_featured_store_days" onChange={(e) => setState({ ...state, days: e.target.value })} type="text" className="wcfm-text" defaultValue={days} />

                        <label>Category</label>

                        <Categories name="wcfm_featured_store_category" category={category} onChange={(category) => setState({ ...state, category })} />

                        <label>Total Price</label>
                        <span>${(days * wcfeatured.pricing.vendor).toFixed(2)}</span>
                    </fieldset>
                    <div className="gap-60" />
                    <button className="wcfm_submit_button" onClick={onSubmit}>Activate Now</button>
                </form>
            </div>
        </div>
    )
}

const VendorFeaturedInfo = (props) => {
    const { start_date, days, category } = props;

    return (
        <div className="wcfm-container" style={{ marginBottom: 40 }}>
            <div className="wcfm-content">
                <h2>Featured Info</h2>
                <div className="gap-10" />
                <dl className="store-featured-info">
                    <dt>Start Date</dt>
                    <dd>{moment(start_date).format('DD MMM, YYYY')}</dd>
                    <dt>Days</dt>
                    <dd>{days}</dd>
                    <dt>Expire on</dt>
                    <dd>{moment(start_date).add(days, 'days').format('DD MMM, YYYY')}</dd>
                    <dt>Category</dt>
                    <dd>{category}</dd>
                </dl>
            </div>
        </div>
    )

}

const ProductItem = (props) => {
    const { product } = props;

    const datepicker = useRef(null);

    useEffect(() => {
        jQuery(datepicker.current).flatpickr({ minDate: 'today' })

    }, [datepicker.current]);

    useEffect(() => {
        jQuery(datepicker.current).change(function (selectedDates, dateStr, instance) {
            if (typeof props.onChange === 'function') {
                props.onChange({ ...product, start: selectedDates.target.value })
            }
        })

    }, [props]);

    const update = (product) => {
        if (typeof props.onChange === 'function') {
            props.onChange(product)
        }
    }

    const on_delete = (index) => {
        if (typeof props.onDelete === 'function') {
            props.onDelete(index)
        }
    }

    const childs = get_sub_categories(product.category);

    return (
        <fieldset className="wcfm-vendor-featured-fieldset wcfm-vendor-featured-fieldset-grid wcfm-vendor-featured-fieldset-product-grid">
            <span className="btn-delete" onClick={() => on_delete(props.number)}>Delete</span>
            <label>Product</label>
            <select defaultValue={product.id} name={`featured_products[${props.number}][id]`} className="wcfm-select" onChange={(e) => update({ ...product, id: e.target.value })} >
                <option value="">Select a product</option>
                {products.map((product) => {
                    return <option value={product.ID}>{product.post_title}</option>
                })}
            </select>
            <label>Start Date</label>
            <input name={`featured_products[${props.number}][start]`} ref={datepicker} type="text" className="wcfm-text wc-multivendor-featured-datepicker" value={product.start} />
            <label>Days</label>
            <input type="text" name={`featured_products[${props.number}][days]`} className="wcfm-text" defaultValue={product.days} onChange={(e) => update({ ...product, days: e.target.value })} />
            <label>Category</label>
            <Categories name={`featured_products[${props.number}][category]`} category={product.category} onChange={(category) => update({ ...product, category })} />

            {childs.length > 0 &&
                <React.Fragment>
                    <label>Sub Category</label>
                    <select defaultValue={product.sub} className="wcfm-select" name={`featured_products[${props.number}][sub]`}>
                        <option value="">Select Sub Category</option>
                        {childs.map(c => <option value={c.term_id}>{c.name}</option>)}
                    </select>
                </React.Fragment>
            }
        </fieldset>
    )
}

const FeaturedProductsAdd = (props) => {
    const [products, setProducts] = useState(props.products);

    const add_feature_product = () => {
        products.push({ id: '', start: '', days: 1, category: '' });
        setProducts([...products])
    }

    const on_update = (product, i) => {
        products[i] = product;
        setProducts([...products])
    }

    const on_delete = (index) => {
        products.splice(index, 1);
        setProducts([...products])
    }

    const on_submit = (e) => {
        let error = null;

        if ( !products.length) {
            e.preventDefault();
            alert('Please add a product.')
        }

        for (const key in products) {
            const product = products[key];

            if (!product.id) {
                error = 'Please select a product';
                continue;
            }

            if (!product.start) {
                error = 'Please select start date';
                continue;
            }

            if (!product.days) {
                error = 'Please specify duration';
                continue;
            }

            if (!product.category) {
                error = 'Please select a category';
                continue;
            }
        }

        const result = products.map(a => a.id);
        let findDuplicates = result.filter((item, index) => result.indexOf(item) != index)

        if (findDuplicates.length) {
            error = 'Please select unique product.';
        }

        if (error) {
            e.preventDefault();
            return alert(error)
        }
    }

    return (
        <div className="wcfm-container">
            <div className="wcfm-content">
                <h2>Add your product at Featured list</h2>
                <div className="gap-20" />
                <form className="wcfm-vendor-featured-form wcfm-vendor-featured-product-form" method="post">
                    <input type="hidden" name="_nonce_featured_products" value={props._nonce} />
                    <div className="wcfm_clearfix" />

                    {products.map((p, index) => <ProductItem onDelete={on_delete} key={index} number={index} product={products[index]} onChange={(product) => on_update(product, index)} />)}

                    <span className="add-new-btn" onClick={add_feature_product}>Add Feature Product</span>

                    <div className="gap-60" />
                    <button className="wcfm_submit_button" onClick={on_submit}>Activate Now</button>
                </form>
            </div>
        </div>
    )
}


const FeaturedProducts = (props) => {
    const products = Object.values(props.products)

    return (
        <table class="table-featured-products">
            <caption>Your Featured Products</caption>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Name</th>
                    <th>Start Date</th>
                    <th>Expired on</th>
                    <th>Days</th>
                    <th>Category</th>
                </tr>
            </thead>

            {products.length == 0 && 
                 <tr>
                    <td colSpan={7} style={{textAlign: 'center'}}>No Products</td>
                </tr>
            }

            {products.length > 0 && products.map((product) => 
                <tr>
                    <td>#{product.id}</td>
                    <td>{product.post_title}</td>
                    <td>{moment(product.start).format('DD MMM, YYYY')}</td>
                    <td>{moment(product.start).add(product.days, 'days').format('DD MMM, YYYY')}</td>
                    <td>{product.days}</td>
                    <td>{product.term_name}</td>
                </tr>
            )}

        </table>
    );
}

const MultivendorFeatured = () => {
    const [state, setState] = useState({
        status: 'loading'
    });

    useEffect(() => {
        jQuery.post(wcfeatured.ajax, { action: "get_featured_data" }, function (data) {
            setState({ ...data });

        }).fail(() => {
            alert('Something wrong.')
        })
    }, [])

    if (state.status === 'loading') {
        return <h2 className="loading">Loading...</h2>
    }

    const { featured_vendor, vendor_featured_products, session_products } = state;

    return (
        <React.Fragment>
            {typeof featured_vendor === 'object' && <VendorFeaturedInfo {...featured_vendor} />}
            {featured_vendor == '' && <FeatureVendorAdd _nonce={state.nonce_vendor_featured} />}

            <FeaturedProducts products={vendor_featured_products} />

            <FeaturedProductsAdd products={session_products} _nonce={state.nonce_featured_products} />
        </React.Fragment>
    )
}

const root_holder = document.getElementById("wc-multivendor-featured");
if ( root_holder ) {
    ReactDOM.render(<MultivendorFeatured />, root_holder);
}
