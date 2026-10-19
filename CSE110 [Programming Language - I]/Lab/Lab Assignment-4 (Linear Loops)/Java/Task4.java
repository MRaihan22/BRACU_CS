import java.util.Scanner;

public class Task4 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Input: ");
        int num = sc.nextInt();
        int count = 0;
        if (num == 0) 
        {
            count = 1;
        } 
        else {
            if (num < 0) 
            {
                num = -num;
            }
            while (num != 0) 
            {
                num = num / 10;
                count++;
            }
        }
        System.out.println("Total digits = " + count);
    }
}
