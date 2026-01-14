# Quick Start Deployment

## 1. Deploy Website
```bash
./deploy.sh
```

## 2. Configure Web Server (Choose One)

### Nginx:
```bash
scp -i ~/Desktop/contabo_key.txt nginx.conf root@144.91.93.170:/etc/nginx/sites-available/sheet.homes
ssh -i ~/Desktop/contabo_key.txt root@144.91.93.170
ln -s /etc/nginx/sites-available/sheet.homes /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
```

### Apache:
```bash
scp -i ~/Desktop/contabo_key.txt apache.conf root@144.91.93.170:/etc/apache2/sites-available/sheet.homes.conf
scp -i ~/Desktop/contabo_key.txt .htaccess root@144.91.93.170:/var/www/sheet.homes/
ssh -i ~/Desktop/contabo_key.txt root@144.91.93.170
a2ensite sheet.homes.conf
a2dissite 000-default.conf
a2enmod rewrite ssl headers
systemctl restart apache2
```

## 3. Setup SSL
```bash
scp -i ~/Desktop/contabo_key.txt setup-ssl.sh root@144.91.93.170:/root/
ssh -i ~/Desktop/contabo_key.txt root@144.91.93.170
chmod +x /root/setup-ssl.sh
/root/setup-ssl.sh
```

**Before running SSL setup, ensure:**
- Domain `sheet.homes` DNS points to 144.91.93.170
- Ports 80 and 443 are open
- Web server is running

## Done! 
Visit https://sheet.homes

