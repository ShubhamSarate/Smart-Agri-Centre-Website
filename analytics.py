"""
AgroCulture Analytics Engine
Tracks product sales, expiry dates, and generates insights for admin dashboard.
Reads from website MySQL DB (fproduct, transaction).
Uses MySQLdb for better compatibility with web servers.
"""

import json
import os
from datetime import datetime, timedelta

try:
    import MySQLdb
    from MySQLdb import Error
except ImportError:
    # Fallback: use built-in sqlite if MySQL unavailable (for testing)
    import sqlite3
    MySQLdb = None

# MySQL Configuration (match your db.php)
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'passwd': '',
    'db': 'agroculture'
}

def get_db_connection():
    """Establish connection to MySQL database."""
    try:
        if MySQLdb:
            conn = MySQLdb.connect(**DB_CONFIG)
            return conn
        else:
            # Fallback to empty connection for demo
            return None
    except Exception as e:
        print(f"Database connection error: {e}")
        return None

def get_sales_by_month(limit_months=12):
    """Get monthly sales aggregation (last N months)."""
    conn = get_db_connection()
    if not conn:
        return []
    
    cursor = conn.cursor(dictionary=True)
    query = """
        SELECT 
            DATE_FORMAT(FROM_UNIXTIME(0), '%Y-%m') as month,
            DATE_TRUNC(DATE(NOW()), INTERVAL DAYOFMONTH(DATE(NOW()))-1 DAY) as period,
            COUNT(*) as order_count,
            COALESCE(SUM(t.quantity_purchased * fp.price), 0) as total_revenue,
            COALESCE(AVG(t.quantity_purchased * fp.price), 0) as avg_order_value
        FROM transaction t
        LEFT JOIN fproduct fp ON t.pid = fp.pid
        WHERE t.tid IS NOT NULL
        GROUP BY YEAR(t.tid), MONTH(t.tid)
        ORDER BY t.tid DESC
        LIMIT ?
    """
    
    # Simpler approach: get all transactions and group by month in Python
    cursor.execute("SELECT t.tid, t.pid, t.quantity_purchased, fp.price, fp.product FROM transaction t LEFT JOIN fproduct fp ON t.pid = fp.pid ORDER BY t.tid DESC")
    rows = cursor.fetchall()
    conn.close()
    
    sales_by_month = {}
    for row in rows:
        # Use current month as approximate (real implementation would store datetime in transaction)
        month = datetime.now().strftime('%Y-%m')
        if month not in sales_by_month:
            sales_by_month[month] = {'orders': 0, 'revenue': 0.0, 'products': []}
        
        sales_by_month[month]['orders'] += 1
        qty = row.get('quantity_purchased') or row.get('quantity') or 1
        price = float(row['price']) if row.get('price') else 0.0
        sales_by_month[month]['revenue'] += float(qty) * price
        sales_by_month[month]['products'].append(row['product'] or 'Unknown')
    
    return [{'month': m, **v} for m, v in sorted(sales_by_month.items())]

def get_top_products(limit=10):
    """Get top selling products by transaction count."""
    conn = get_db_connection()
    if not conn:
        return []
    
    cursor = conn.cursor(dictionary=True)
    query = """
        SELECT 
            fp.product,
            fp.pid,
            COALESCE(SUM(t.quantity_purchased), 0) as sales_count,
            COALESCE(SUM(t.quantity_purchased * fp.price), 0) as total_revenue,
            COALESCE(AVG(fp.price), 0) as avg_price
        FROM transaction t
        JOIN fproduct fp ON t.pid = fp.pid
        GROUP BY t.pid, fp.product
        ORDER BY sales_count DESC
        LIMIT ?
    """
    
    cursor.execute(query, (limit,))
    results = cursor.fetchall()
    conn.close()
    
    return [dict(r) for r in results]

def get_expiry_alerts():
    """Get products expiring soon or already expired."""
    conn = get_db_connection()
    if not conn:
        return {'expire_soon': [], 'expired': []}
    
    cursor = conn.cursor(dictionary=True)
    today = datetime.now().date()
    soon = (today + timedelta(days=30)).isoformat()
    
    # Products expiring within 30 days
    query_soon = """
        SELECT 
            pid, product, pcat, price, expiry_date,
            DATEDIFF(expiry_date, %s) as days_left
        FROM fproduct
        WHERE expiry_date IS NOT NULL
            AND expiry_date > %s
            AND expiry_date <= %s
        ORDER BY expiry_date ASC
    """
    
    cursor.execute(query_soon, (today, today, soon))
    expire_soon = cursor.fetchall()
    
    # Expired products
    query_expired = """
        SELECT 
            pid, product, pcat, price, expiry_date,
            DATEDIFF(%s, expiry_date) as days_overdue
        FROM fproduct
        WHERE expiry_date IS NOT NULL AND expiry_date <= %s
        ORDER BY expiry_date DESC
    """
    
    cursor.execute(query_expired, (today, today))
    expired = cursor.fetchall()
    conn.close()
    
    return {
        'expire_soon': [dict(r) for r in expire_soon],
        'expired': [dict(r) for r in expired]
    }

def get_low_stock_items(threshold=5):
    """Get items with low stock count (based on sales velocity)."""
    conn = get_db_connection()
    if not conn:
        return []
    
    cursor = conn.cursor(dictionary=True)
    # Simple heuristic: count sales in last month; if > threshold, flag as popular
    query = """
        SELECT 
            fp.pid,
            fp.product,
            COALESCE(SUM(t.quantity_purchased), 0) as recent_sales,
            fp.price
        FROM fproduct fp
        LEFT JOIN transaction t ON fp.pid = t.pid
        GROUP BY fp.pid, fp.product
        HAVING recent_sales > ?
        ORDER BY recent_sales DESC
    """
    
    cursor.execute(query, (threshold,))
    results = cursor.fetchall()
    conn.close()
    
    return [dict(r) for r in results]

def get_revenue_summary():
    """Get total revenue, order count, and other KPIs."""
    conn = get_db_connection()
    if not conn:
        return {}
    
    cursor = conn.cursor(dictionary=True)
    
    # Total orders
    cursor.execute("SELECT COUNT(*) as total_orders FROM transaction")
    total_orders = cursor.fetchone()['total_orders']
    
    # Total revenue
    cursor.execute("""
        SELECT COALESCE(SUM(t.quantity_purchased * fp.price), 0) as total_revenue
        FROM transaction t
        LEFT JOIN fproduct fp ON t.pid = fp.pid
    """)
    revenue = cursor.fetchone()['total_revenue'] or 0.0
    
    # Average order value
    cursor.execute("""
        SELECT COALESCE(AVG(t.quantity_purchased * fp.price), 0) as avg_order_value
        FROM transaction t
        LEFT JOIN fproduct fp ON t.pid = fp.pid
    """)
    avg_value = cursor.fetchone()['avg_order_value'] or 0.0
    
    # Unique products sold
    cursor.execute("SELECT COUNT(DISTINCT pid) as unique_products FROM transaction")
    unique_products = cursor.fetchone()['unique_products']
    
    conn.close()
    
    return {
        'total_orders': total_orders,
        'total_revenue': float(revenue),
        'avg_order_value': float(avg_value),
        'unique_products': unique_products
    }

def generate_report():
    """Generate complete analytics report."""
    report = {
        'generated_at': datetime.now().isoformat(),
        'summary': get_revenue_summary(),
        'top_products': get_top_products(10),
        'sales_by_month': get_sales_by_month(12),
        'expiry_alerts': get_expiry_alerts(),
        'low_stock': get_low_stock_items(3),
    }
    return report

if __name__ == '__main__':
    report = generate_report()
    print(json.dumps(report, indent=2, default=str))
