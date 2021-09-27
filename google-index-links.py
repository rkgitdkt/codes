import requests
from bs4 import BeautifulSoup
search = input('Search Title : ')
# main_keyword = input('Main Keyword : ')

# total records
results = 20

# get raw data
page = requests.get(f"https://www.google.com/search?q={search}&num={results}")
soup = BeautifulSoup(page.content, "html.parser")

# find all anchor tags
links = soup.findAll("a")
fetched_link = []
for link in links :
    link_href = link.get('href')

    """
        1. link should contain url?q=
        2. webcache shhould include
        3. skip youtube videos.
        4. remove google link   
    """
    if not 'google' in link_href and "url?q=" in link_href and not "webcache" in link_href and not 'youtube' in link_href:
        # append after remove url?q & &sa=U
        fetched_link.append(link_href.split("?q=")[1].split("&sa=U")[0])

for link in fetched_link : print(link)