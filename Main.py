'''
Created on 2012-06-10

@author: chris
'''
import re
import reddit
import urllib
import json
import MySQLdb as mdb
import sys
from selenium import webdriver


class Earth():
    
    db = None
    places_count = 0
    places_found = 0
    browser = None


    def __init__(self):
        self.browser = webdriver.Firefox()

    def init_db(self):
        try:
            self.db = mdb.connect('localhost', 'USER', 
                'PASSWORD', 'earth')
            
            self.db.query("SELECT VERSION()")
            result = self.db.use_result()
        
            print "MySQL version: %s" % result.fetch_row()[0]
        except mdb.Error, e:
            print "Error %d: %s" % (e.args[0], e.args[1])
            sys.exit(1)
            
            
    def entry_exists(self, name):
        cur = self.db.cursor(mdb.cursors.DictCursor)
        e = "SELECT name FROM places WHERE name = " + name
        try:
            cur.execute(e)
            if cur.rowcount > 0:
                return True
            else:
                return False
        except:
            return False
        
        
    def clean_link(self, link):
        print link
        matches = re.search('(\.jpg)$|(\.png)$|(\.jpeg)$', link)
        if matches != None: 
            return link
        else:
            self.browser.get(link)
            images = self.browser.find_elements_by_xpath("//img")
            max_size = 0
            biggest_image = None
            for img in images:
                if img.size['width'] > max_size:
                    max_size = img.size['width']
                    biggest_image = img
            if biggest_image != None:
                link = biggest_image.get_attribute('src')
            return link
    
    def store_place(self, submission):
        matches = re.search('.*(?=\[\d)', submission.title)
        if matches != None:
            title = matches.group(0)
            link = submission.url
            reddit_link = submission.permalink
            self.places_count = self.places_count + 1
            link = self.clean_link(str(link))
            formatted_title = re.sub(',|\.', '', title)
            formatted_title = (re.sub('\s+(?=\S+)', '+', formatted_title)).encode("ascii", "ignore")
            
            url = 'https://maps.googleapis.com/maps/api/geocode/json?address='+formatted_title+'&sensor=false'
            page = urllib.urlopen(url);
            j = json.loads(page.read())
            if j['status'] == "ZERO_RESULTS":
                print 'no results'
                #Do something else
            else:
                self.places_found = self.places_found + 1
                results = j['results']
                if len(results) > 0:
                    loc = results[0]['geometry']['location']
                    lat = loc['lat']
                    lng = loc['lng']
                   
                    entry_title = "'"+self.db.escape_string(title)+"'"
                    entry_link = "'"+self.db.escape_string(link)+"'"
                    reddit_link = "'"+self.db.escape_string(reddit_link)+"'"
                    if not self.entry_exists(entry_title):
                        cur = self.db.cursor(mdb.cursors.DictCursor)
                        try:
        
                        
                            q = "INSERT INTO places(name, lat, lng, link, reddit, time) VALUES("+entry_title+", "+str(lat)+", "+str(lng)+", "+entry_link+", "+reddit_link+", NOW())"
                            cur.execute(q)
                            print "Inserted: name: " + title + " lat: " + str(lat) + ' lng: ' + str(lng) + " l: " + link
                        except UnicodeEncodeError:
                            pass
            
            
            
            
            
    def read_reddit(self):
        r = reddit.Reddit(user_agent="EarthPorn Mapper .01 by /u/rozap")
        submissions = r.get_subreddit('earthporn').get_hot(limit=30)
    
        for s in submissions:
            self.store_place(s)
                
        
        print "Places Found: " + str(self.places_found) + " Total: " + str(self.places_count)
        
        
    def cleanup(self):
        self.browser.close()







def main():
    print "Starting Earth"
    earth = Earth()
    earth.init_db()
    earth.read_reddit()
    earth.cleanup()


if __name__ == '__main__':
    main()
