-- Companies table
CREATE TABLE IF NOT EXISTS companies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    address TEXT NOT NULL,
    state TEXT NOT NULL,
    district TEXT NOT NULL,
    gst_no TEXT NOT NULL
);

-- Tools table
CREATE TABLE IF NOT EXISTS tools (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    rate REAL NOT NULL,
    is_retailer INTEGER DEFAULT 0
);

-- Invoices table
CREATE TABLE IF NOT EXISTS invoices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    date DATE NOT NULL,
    type TEXT NOT NULL, -- invoice, proforma, quotation
    total REAL NOT NULL,
    tax REAL NOT NULL,
    FOREIGN KEY(company_id) REFERENCES companies(id)
);

-- Invoice items table
CREATE TABLE IF NOT EXISTS invoice_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_id INTEGER NOT NULL,
    tool_id INTEGER NOT NULL,
    qty INTEGER NOT NULL,
    rate REAL NOT NULL,
    discount REAL NOT NULL,
    tax REAL NOT NULL,
    FOREIGN KEY(invoice_id) REFERENCES invoices(id),
    FOREIGN KEY(tool_id) REFERENCES tools(id)
);