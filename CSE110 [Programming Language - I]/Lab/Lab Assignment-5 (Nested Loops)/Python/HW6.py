start = int(input("Start: "))
stop = int(input("Stop: "))
for num in range(start, stop+1):
    print("Times Table of", num,":")
    for i in range(1, 10+1):
        print(num, "x", i, "=", (num * i))