# Deployment Guide for Sheet Homes Website

This guide will help you deploy the Sheet Homes website to your server with SSL and clean URLs.

## Prerequisites

- SSH access to server (144.91.93.170)
- SSH key file at `~/Desktop/contabo_key.txt`
- Domain name `sheet.homes` pointing to server IP
- Root access on the server

## Step 1: Deploy Website Files

Run the deployment script from your local machine:

```bash
./deploy.sh
```

This script will:
- Upload all website files to `/var/www/sheet.homes` on the server
- Set proper file permissions
- Use your SSH key for authentication

## Step 2: Configure Web Server

### For Nginx (Recommended):

1. Copy the nginx configuration to the server:
```bash
scp -i ~/Desktop/contabo_key.txt nginx.conf root@144.91.93.170:/etc/nginx/sites-available/sheet.homes
```

2. SSH into the server:
```bash
ssh -i ~/Desktop/contabo_key.txt root@144.91.93.170
```

3. On the server, create symlink and remove default:
```bash
ln -s /etc/nginx/sites-available/sheet.homes /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t  # Test configuration
systemctl restart nginx
```

### For Apache:

1. Copy the Apache configuration to the server:
```bash
scp -i ~/Desktop/contabo_key.txt apache.conf root@144.91.93.170:/etc/apache2/sites-available/sheet.homes.conf
```

2. SSH into the server and run:
```bash
ssh -i ~/Desktop/contabo_key.txt root@144.91.93.170
a2ensite sheet.homes.conf
a2dissite 000-default.conf
a2enmod rewrite ssl headers
systemctl restart apache2
```

3. Copy .htaccess file:
```bash
scp -i ~/Desktop/contabo_key.txt .htaccess root@144.91.93.170:/var/www/sheet.homes/
```

## Step 3: Set Up SSL Certificate

1. Copy the SSL setup script to the server:
```bash
scp -i ~/Desktop/contabo_key.txt setup-ssl.sh root@144.91.93.170:/root/
```

2. SSH into the server and run:
```bash
ssh -i ~/Desktop/contabo_key.txt root@144.91.93.170
chmod +x /root/setup-ssl.sh
/root/setup-ssl.sh
```

**Important:** Before running the SSL script, make sure:
- Your domain `sheet.homes` is pointing to the server IP (144.91.93.170)
- Ports 80 and 443 are open in your firewall
- The web server is running and accessible

The script will:
- Install Certbot
- Obtain SSL certificate from Let's Encrypt
- Configure automatic renewal
- Set up HTTPS redirect

## Step 4: Verify Deployment

1. Check website is accessible:
   - HTTP: http://sheet.homes (should redirect to HTTPS)
   - HTTPS: https://sheet.homes

2. Test clean URLs:
   - https://sheet.homes/about
   - https://sheet.homes/services
   - https://sheet.homes/properties
   - etc.

3. Verify SSL certificate:
```bash
openssl s_client -connect sheet.homes:443 -servername sheet.homes
```

## Troubleshooting

### SSL Certificate Issues

If SSL setup fails:
1. Ensure domain DNS is properly configured
2. Check firewall allows ports 80 and 443
3. Verify web server is running: `systemctl status nginx` or `systemctl status apache2`

### Clean URLs Not Working

For Apache:
- Ensure mod_rewrite is enabled: `a2enmod rewrite`
- Check .htaccess file exists and has correct permissions
- Verify AllowOverride is set to All in Apache config

For Nginx:
- Check nginx configuration syntax: `nginx -t`
- Verify try_files directive is correct in nginx.conf

### Permission Issues

If files are not accessible:
```bash
chown -R www-data:www-data /var/www/sheet.homes
chmod -R 755 /var/www/sheet.homes
```

## Maintenance

### Renew SSL Certificate

SSL certificates auto-renew, but you can manually renew:
```bash
certbot renew
```

### Update Website

Simply run the deployment script again:
```bash
./deploy.sh
```

## File Structure

```
/var/www/sheet.homes/
├── index.html
├── about.html
├── services.html
├── properties.html
├── property-single.html
├── service-details.html
├── founder.html
├── contact.html
├── starter-page.html
├── assets/
│   ├── css/
│   ├── js/
│   ├── img/
│   └── vendor/
└── .htaccess (Apache only)
```

## Support

For issues or questions, check:
- Nginx error logs: `/var/log/nginx/sheet.homes.error.log`
- Apache error logs: `/var/log/apache2/sheet.homes_error.log`
- System logs: `journalctl -u nginx` or `journalctl -u apache2`

