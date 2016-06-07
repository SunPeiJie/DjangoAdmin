#coding:utf-8
import sqlite3
import redis
redisServer =  redis.StrictRedis(host='192.168.10.251', port=6379)


from django.contrib import auth
from django.contrib.auth.decorators import user_passes_test, login_required
from django.shortcuts import render
from django.http.response import HttpResponse
from django.http import HttpResponseRedirect
from django.core.urlresolvers import reverse
from django.contrib.auth.models import User
import json
#导入包装的csrf请求，对跨站攻击脚本做处理
from django.views.decorators.csrf import csrf_exempt
import time

@login_required(login_url="/login/")
@csrf_exempt
def index(request):
    if request.method == 'POST':
        postdata = request.POST
        msg = postdata.get('message')
        lastindex = postdata.get('lastindex')
        data ={}
        if msg:
            #lsize = redisServer.llen('index_chatdata')
            user = request.user.username
            data['username'] = user
            data['message'] = msg
            data['time'] = time.asctime()
            #print json.dumps(data)
            lsize = redisServer.rpush('index_chatdata',json.dumps(data))
            data['id'] = str(lsize)
            return HttpResponse(json.dumps(data))
        if lastindex:
            lastindex = int(lastindex)
            lsize = redisServer.llen('index_chatdata')
            datalist =[]
            if lastindex ==lsize -1:
                return HttpResponse(json.dumps(data))
            if lastindex < 0:
                data = redisServer.lrange('index_chatdata',lsize-10,lsize)
                lastindex = lsize - 10
            else:
                data = redisServer.lrange('index_chatdata',lastindex,lsize)
            for d in data:
                data_dict =json.loads(d)
                data_dict['id'] = str(lastindex)
                lastindex = lastindex+1
                datalist.append(data_dict)
            return HttpResponse(json.dumps(datalist))
        return HttpResponse(json.dumps(data))
    return render(
        request,
        "NiceAdmin/index.html",
    )

@csrf_exempt
def login(request):
    if request.method == 'POST':
        postdata = request.POST
        username = postdata.get('name', '')
        password = postdata.get('pwd', '')
        user = auth.authenticate(username=username, password=password)
        if user is not None:
            auth.login(request, user)
            return HttpResponse(json.dumps({"msg":"true","url":"/index/"}))
        return HttpResponse(json.dumps({"msg":"false"}))

    return render(
        request,
        "NiceAdmin/login.html",
    )

@login_required(login_url="/login/")
def mediaInfo(request):
    return render(
        request,
        "NiceAdmin/MediaInfo.html",
    )


def logout(request):
    auth.logout(request)
    return HttpResponseRedirect(reverse('login'))


@login_required(login_url="/login/")
def  tabledata(request):
    print request
    return render(
        request,
        "NiceAdmin/tabledata.html",
    )



def countFromTable():
    with sqlite3.connect('MediaInfoDB.db') as conn:
        cu = conn.cursor()
        cu.execute('select count(1) from VideoInfo')
        data = cu.fetchone()
        cu.close()
        return int(data[0])

def selectFromMediaInfoDB(start,length):
    with sqlite3.connect('MediaInfoDB.db') as conn:
        cu = conn.cursor()
        sql = 'select * from VideoInfo  LIMIT %s,%s' % (start,length)
        cu.execute(sql)
        data = cu.fetchall()
        l = [list(x) for x in data]
        cu.close()
        return l

def  server_side(request):
    recordsTotal = countFromTable()
    GetData = request.GET
    draw = GetData.get("draw")
    start = request.GET.get("start",0)
    length= request.GET.get("length")
    search = request.GET.get("search[value]")
    columns = request.GET.get("columns[1]")
    #print draw,start,length,search,columns
    data = selectFromMediaInfoDB(start,length)
    #print(data)


    return HttpResponse(json.dumps({
  "draw": draw,
  "recordsTotal": recordsTotal,
  "recordsFiltered": recordsTotal,
  "data": data
}))


def selectDataFromMediaInfoDB(start,length):
    with sqlite3.connect('MediaInfoDB.db') as conn:
        cu = conn.cursor()
        sql = 'select * from VideoInfo  LIMIT %s,%s' % (start,length)
        cu.execute(sql)
        data = cu.fetchall()
        datalist =[]
        for x in data:
            d ={}
            d['ID'] = x[0]
            d['Duration'] = x[1]
            d['Width'] = x[2]
            d['Height'] = x[3]
            d['FPS'] = x[4]
            datalist.append(d)
        cu.close()
        return datalist

@login_required(login_url="/login/")
@csrf_exempt
def  dtgrid(request):
    if request.method == 'POST':
        postdata = request.POST['dtGridPager']
        postdata=json.loads(postdata)
        pageSize =  postdata["pageSize"]
        startRecord = postdata["startRecord"]
        recordCount = countFromTable()
        postdata["recordCount"] = recordCount
        addone = 0
        if recordCount%pageSize>0:
            addone =1

        postdata["pageCount"] = recordCount/pageSize+addone
        postdata['exhibitDatas'] = selectDataFromMediaInfoDB(startRecord,pageSize)
        postdata['isSuccess'] = True
        return HttpResponse(json.dumps(postdata))

    return render(
            request,
            "NiceAdmin/dtgrid_test.html",
        )

@login_required(login_url="/login/")
@csrf_exempt
def ComputerInfo(request):
    if request.method == 'POST':
        postdata = request.POST
        IP = postdata.get('IP', '')
        keys =  'ComputerList:'+IP
        if IP is not None:
            computerdict ={}
            ComputerList = redisServer.keys('ComputerList:*')
            for c in ComputerList:
                datajson = redisServer.get(c)
                d = c.split(":")
                computerdict[d[1]] = (json.loads(datajson))
            return HttpResponse(json.dumps(computerdict))


    return render(
        request,
        "NiceAdmin/ComputerInfo.html",
    )






@csrf_exempt
def Register(request):
    if request.method == 'POST':
        postdata = request.POST
        username = postdata.get('name', '')
        password = postdata.get('pwd', '')
        #user = User.objects.get(username__exact=username)
        try:
            user = User.objects.create_user(username, '', password)
            if user:
                # user.is_staff = True
                # user.is_superuser =True
                user.save()
                return HttpResponse(json.dumps({"msg":"true","url":"/login/"}))
        except Exception, e:
            return HttpResponse(json.dumps({"msg":"false"}))
        return HttpResponse(json.dumps({"msg":"false"}))

    return render(
        request,
        "NiceAdmin/register.html",
    )
# Create your views here.
